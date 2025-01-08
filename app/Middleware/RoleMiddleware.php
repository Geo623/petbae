<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
   public function handle($request, Closure $next, $role)
{
    if ($request->user()->role !== $role) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    return $next($request);
}
}
