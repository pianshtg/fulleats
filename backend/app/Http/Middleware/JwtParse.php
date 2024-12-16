<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

class JwtParse
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = $request->header('Authorization');

        if (!$authorization || !str_starts_with($authorization, 'Bearer')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = explode(' ', $authorization)[1];

        try {
            // Decode JWT token
            $decoded = JWT::decode($token, new Key('', 'none'));
            $auth0Id = $decoded->sub;

            // Find the user by auth0Id in the database
            $user = User::where('auth0_id', $auth0Id)->first();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Attach user details to the request
            $request->merge([
                'auth0Id' => $auth0Id,
                'userId' => $user->id,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
