<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\SharedRoutine\BaseController;
use App\Models\License;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseControllerxx extends BaseController
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

        // Atomic claim: only an unused, unexpired key can be activated
        $claimed = License::where('license_key', strtoupper($data['licenseKey']))
            ->whereNull('activated_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->update([
                'company_name' => trim($data['companyName']),
                'email'        => trim($data['email']),
                'activated_at' => now(),
            ]);

        if (! $claimed) {
            return response()->json(['message' => 'Invalid, expired or already used license key.'], 422);
        }

        return $this->payload(License::current());
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