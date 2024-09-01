<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, string $role, Closure $next)
    {
        if ($request->user() && $request->user()->role === $role) {
            return $next($request);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
