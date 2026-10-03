<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Exception;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private ?string $fallbackModel;

    private const CACHE_TTL_HOURS = 6;
    private const MAX_ATTEMPTS = 4;
    private const REQUEST_TIMEOUT = 30;
    private const MAX_RATE_LIMIT_WAIT = 15; // cap seconds we'll wait on a 429

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key')
            ?? throw new \RuntimeException('GEMINI_API_KEY is missing. Please set it in your .env file.');

        $this->model = config('services.gemini.model')
            ?? throw new \RuntimeException('GEMINI_MODEL is missing. Please set it in your .env file.');

        $this->fallbackModel = config('services.gemini.fallback_model');
    }

    public function chat(
        string $message,
        array $history = []
    ): string {

        $contents = $this->buildContents($message, $history);

        $payload = [

            'systemInstruction' => [
                'parts' => [
                    [
                        'text' => WebsiteKnowledge::get()
                    ]
                ]
            ],

            'contents' => $contents,

            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 1000,
            ],

        ];

        /*
        |--------------------------------------------------------------------------
        | Cache identical questions to reduce API load and cost
        |--------------------------------------------------------------------------
        */

        $cacheKey = 'gemini_'
            . md5($this->model . '|' . $message . '|' . json_encode($history));

        return Cache::remember(
            $cacheKey,
            now()->addHours(self::CACHE_TTL_HOURS),
            fn () => $this->requestWithRetries($payload)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Build the "contents" array from history + current message
    |--------------------------------------------------------------------------
    */
    private function buildContents(string $message, array $history): array
    {
        $contents = [];

        foreach ($history as $item) {

            if (
                !isset($item['role']) ||
                !isset($item['text'])
            ) {
                continue;
            }

            $role = $item['role'] === 'user'
                ? 'user'
                : 'model';

            $contents[] = [
                'role' => $role,
                'parts' => [
                    [
                        'text' => $item['text']
                    ]
                ]
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => $message
                ]
            ]
        ];

        return $contents;
    }

    /*
    |--------------------------------------------------------------------------
    | Attempt the request against the primary model with retries +
    | exponential backoff, then fall back to a secondary model if the
    | primary is still overloaded or rate-limited after all attempts.
    |--------------------------------------------------------------------------
    */
    private function requestWithRetries(array $payload): string
    {
        $response = $this->attemptRequest($this->model, $payload);

        $stillFailing = $this->isOverloaded($response) || $this->isRateLimited($response);

        if ($stillFailing && $this->fallbackModel) {

            Log::warning('Gemini primary model unavailable, trying fallback', [
                'primary_model' => $this->model,
                'fallback_model' => $this->fallbackModel,
                'reason' => $this->isRateLimited($response) ? 'rate_limited' : 'overloaded',
            ]);

            $response = $this->attemptRequest($this->fallbackModel, $payload);
        }

        if ($response->failed()) {

            Log::error('Gemini API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($this->isRateLimited($response)) {
                throw new Exception(
                    'The assistant has reached its usage limit for now. Please try again shortly.'
                );
            }

            if ($this->isOverloaded($response)) {
                throw new Exception(
                    'Gemini is currently overloaded. Please try again in a moment.'
                );
            }

            throw new Exception(
                'Gemini API Error: ' . $response->body()
            );
        }

        $data = $response->json();

        return
            $data['candidates'][0]['content']['parts'][0]['text']
            ?? 'I could not generate an answer.';
    }

    /*
    |--------------------------------------------------------------------------
    | Send the HTTP request to a given model, retrying on transient
    | failures (connection errors, 503 overload) with exponential backoff,
    | and on 429 rate limits with a single capped wait.
    |--------------------------------------------------------------------------
    */
    private function attemptRequest(string $model, array $payload): Response
    {
        $url =
            'https://generativelanguage.googleapis.com'
            . '/v1beta/models/'
            . $model
            . ':generateContent?key='
            . $this->apiKey;

        $response = null;
        $rateLimitRetries = 0;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {

            try {
                $response = Http::timeout(self::REQUEST_TIMEOUT)
                    ->acceptJson()
                    ->withOptions([
                        'verify' => storage_path('cacert.pem'),
                    ])
                    ->post($url, $payload);

            } catch (ConnectionException $e) {

                Log::warning('Gemini connection error', [
                    'model' => $model,
                    'attempt' => $attempt,
                    'message' => $e->getMessage(),
                ]);

                if ($attempt === self::MAX_ATTEMPTS) {
                    throw new Exception(
                        'Could not reach the Gemini API (timeout or network issue): '
                        . $e->getMessage()
                    );
                }

                $this->backoff($attempt);
                continue;
            }

            if ($response->successful()) {
                return $response;
            }

            // Rate limit: only retry once, capped wait, then give up on this model
            if ($this->isRateLimited($response)) {

                if ($rateLimitRetries >= 1) {
                    return $response;
                }

                $rateLimitRetries++;
                $wait = min($this->getRetryDelay($response), self::MAX_RATE_LIMIT_WAIT);

                Log::info('Gemini rate limited, waiting before single retry', [
                    'model' => $model,
                    'wait_seconds' => $wait,
                ]);

                sleep($wait);
                continue;
            }

            // Overload (503): retry with exponential backoff up to MAX_ATTEMPTS
            if (!$this->isOverloaded($response) || $attempt === self::MAX_ATTEMPTS) {
                return $response;
            }

            Log::info('Gemini overloaded, retrying', [
                'model' => $model,
                'attempt' => $attempt,
            ]);

            $this->backoff($attempt);
        }

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | Exponential backoff: 1s, 2s, 4s, 8s...
    |--------------------------------------------------------------------------
    */
    private function backoff(int $attempt): void
    {
        usleep((int) (pow(2, $attempt) * 500000));
    }

    /*
    |--------------------------------------------------------------------------
    | Check whether a response indicates the model is overloaded (503)
    |--------------------------------------------------------------------------
    */
    private function isOverloaded(Response $response): bool
    {
        return $response->status() === 503
            || $response->json('error.status') === 'UNAVAILABLE';
    }

    /*
    |--------------------------------------------------------------------------
    | Check whether a response indicates a quota/rate limit (429)
    |--------------------------------------------------------------------------
    */
    private function isRateLimited(Response $response): bool
    {
        return $response->status() === 429
            || $response->json('error.status') === 'RESOURCE_EXHAUSTED';
    }

    /*
    |--------------------------------------------------------------------------
    | Extract Google's suggested retry delay from the error details,
    | e.g. "41s" -> 41. Falls back to 5 seconds if not present.
    |--------------------------------------------------------------------------
    */
    private function getRetryDelay(Response $response): int
    {
        $details = $response->json('error.details') ?? [];

        foreach ($details as $detail) {
            if (($detail['@type'] ?? '') === 'type.googleapis.com/google.rpc.RetryInfo') {
                $seconds = (int) rtrim($detail['retryDelay'] ?? '5s', 's');
                return max($seconds, 1);
            }
        }

        return 5;
    }
}