<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class WebsiteChatController extends Controller
{
    public function __construct(
        private GeminiService $geminiService
    ) {
    }


    public function chat(
        Request $request
    ): JsonResponse {

        $validated = $request->validate([

            'message' => [
                'required',
                'string',
                'max:5000'
            ],

            'history' => [
                'nullable',
                'array'
            ],

            'history.*.role' => [
                'required',
                'in:user,model'
            ],

            'history.*.text' => [
                'required',
                'string'
            ],

        ]);


        try {

            $answer = $this->geminiService->chat(

                $validated['message'],

                $validated['history'] ?? []

            );


            return response()->json([

                'success' => true,

                'message' => $answer

            ]);

        } catch (Throwable $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'The AI service is currently unavailable.',

                'error' =>
                    config('app.debug')
                        ? $e->getMessage()
                        : null

            ], 500);
        }
    }

//    private const FILE = 'ai/website-knowledge.txt';

//     public static function get(): string
//     {
//         if (!Storage::disk('local')->exists(self::FILE)) {
//             throw new RuntimeException(
//                 'Website knowledge file not found: ' . self::FILE
//             );
//         }

//         return Storage::disk('local')->get(self::FILE);
//     }
}