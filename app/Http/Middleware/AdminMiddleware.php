<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'message' => 'Forbidden—admin access required.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
