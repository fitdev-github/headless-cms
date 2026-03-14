<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class CorsHeaders
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            return $this->addHeaders(response('', 200), $request);
        }

        $response = $next($request);
        return $this->addHeaders($response, $request);
    }

    private function addHeaders($response, Request $request)
    {
        try {
            $origins = Setting::get('cors_origins', '*');
        } catch (\Throwable $e) {
            $origins = '*';
        }

        $origin = $request->header('Origin');

        if ($origins === '*') {
            $allowOrigin = '*';
        } elseif ($origin) {
            $allowed     = array_map('trim', explode(',', $origins));
            $allowOrigin = in_array($origin, $allowed) ? $origin : '';
        } else {
            $allowOrigin = '';
        }

        if ($allowOrigin) {
            $response->headers->set('Access-Control-Allow-Origin', $allowOrigin);
        }
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept, X-Requested-With');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
}
