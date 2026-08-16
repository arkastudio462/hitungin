<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AndroidApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Api-Key');

        if (! $apiKey || ! hash_equals(config('services.android.api_key', ''), $apiKey)) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        $token = $request->bearerToken();

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken) {
                Auth::setUser($accessToken->tokenable);
            }
        }

        if (! $request->user()) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        return $next($request);
    }
}
