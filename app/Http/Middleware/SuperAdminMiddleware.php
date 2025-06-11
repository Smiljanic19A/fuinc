<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if ($request->header('Authorization') == null || $request->header('Authorization') !== "Aa131213121312") {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Check if user is superadmin
        if (!auth()->user()->isSuperAdmin()) {
            return response()->json([
                'message' => 'Access denied. Superadmin privileges required.'
            ], 403);
        }

        return $next($request);
    }
}
