<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TenantMiddleware as Middleware;

class TenantMiddleware extends Middleware
{
    public function handle($request, Closure $next)
{
    if ($request->user()->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    return $next($request);
}
}