<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        foreach ($roles as $role) {
            if ($user->hasRole($role)) return $next($request);
        }
        return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }
}
