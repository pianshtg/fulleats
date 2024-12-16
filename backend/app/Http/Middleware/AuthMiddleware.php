<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Exception;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken(); // Retrieve the Bearer token from the Authorization header

        if (!$token) {
            return response()->json(['message' => 'Unauthorized: No token provided'], 401);
        }

        try {
            // Configure the Auth0 SDK
            $config = new SdkConfiguration(
                strategy: SdkConfiguration::STRATEGY_API,
                domain: config('auth0.domain'),
                audience: [config('auth0.audience'), '7ZgaRXFVs6KdcIXK7U4R2hXS2fPBmi6S']
            );

            $auth0 = new Auth0($config);

            // Decode and verify the token
            $decoded = $auth0->decode($token);

            // Extract claims from the decoded token
            $claims = $decoded->toArray(); // Convert the token to an array
            logger()->info('Token Decoded Claims:', ['claims' => $claims]);
            $sub = $claims['sub'] ?? null; // Extract the `sub` (subject) claim

            if (!$sub) {
                return response()->json(['message' => 'Unauthorized: Missing subject in token'], 401);
            }

            // Attach the userId (sub) to the request
            $request->merge(['userId' => $sub]);

            return $next($request);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unauthorized: ' . $e->getMessage()], 401);
        }
    }
}
