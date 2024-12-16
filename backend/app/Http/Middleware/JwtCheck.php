<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JwtCheck
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header('Authorization');

        if (!$authorization || !str_starts_with($authorization, 'Bearer')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify the token with Auth0's introspection endpoint
        $token = explode(' ', $authorization)[1];

        $response = Http::withToken($token)->get(config('AUTH0_ISSUER_BASE_URL') . '/userinfo');

        if ($response->failed()) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
