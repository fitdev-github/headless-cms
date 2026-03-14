<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (!file_exists(storage_path('app/installed.lock'))) {
            return redirect('/setup');
        }
        return $next($request);
    }
}
