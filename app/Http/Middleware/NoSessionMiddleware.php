<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoSessionMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        config(['session.driver' => 'array']);
        return $next($request);
    }
}
