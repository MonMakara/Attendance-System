<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Check if user is authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 2. Check if the user's role is in the allowed $roles array
        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Unauthorized action. You do not have the required role.'
            ], 403);
        }

        return $next($request);
    }
}
