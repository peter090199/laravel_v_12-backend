<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\License;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends BaseController
{
    // GET /api/license/status
    public function status(): JsonResponse
    {
        return $this->payload(License::current());
    }

    // POST /api/license/activate
    public function activate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'companyName' => ['required', 'string', 'min:2', 'max:150'],
            'email'       => ['required', 'email', 'max:150'],
            'licenseKey'  => ['required', 'regex:/^[A-Za-z0-9-]{10,40}$/'],
        ]);

        if (License::current()) {
            return response()->json(['message' => 'A license is already active.'], 409);
        }

        $license = License::where('license_key', strtoupper(trim($data['licenseKey'])))->first();

        if (! $license) {
            return $this->fail('License key not found.');
        }
        if ($license->activated_at) {
            return $this->fail('This license key has already been used.');
        }
        if ($license->expires_at && $license->expires_at->isPast()) {
            return $this->fail('This license key has expired.');
        }

        // Atomic claim: guards against two simultaneous activations of the same key
        $claimed = License::whereKey($license->id)
            ->whereNull('activated_at')
            ->update([
                'company_name' => trim($data['companyName']),
                'email'        => trim($data['email']),
                'activated_at' => now(),
            ]);

        if (! $claimed) {
            return $this->fail('This license key has already been used.');
        }

        return $this->payload(License::current());
    }

    private function fail(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 422);
    }

    private function payload(?License $license): JsonResponse
    {
        return response()->json([
            'activated'   => (bool) $license,
            'companyName' => $license?->company_name,
            'expiresAt'   => $license?->expires_at,
        ]);
    }
}