<?php

namespace App\Http\Middleware;

use App\Models\License;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLicensed
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! License::current()) {
            return response()->json(['message' => 'No active license.'], 403);
        }

        return $next($request);
    }
}