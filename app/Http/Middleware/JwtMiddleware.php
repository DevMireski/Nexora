<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) return response()->json(['success' => false, 'message' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Token error: '.$e->getMessage()], 401);
        }
        return $next($request);
    }
}
