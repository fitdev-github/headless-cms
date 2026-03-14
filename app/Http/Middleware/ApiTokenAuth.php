<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    private function resolveAction(Request $request, string $slug): string
    {
        $method = $request->method();
        $id     = $request->route('id');

        if ($slug === 'upload') {
            return match ($method) {
                'GET'    => $id ? 'upload.findOne' : 'upload.find',
                'POST'   => 'upload.create',
                'DELETE' => 'upload.delete',
                default  => 'upload.find',
            };
        }

        return match ($method) {
            'GET'    => $id ? 'findOne' : 'find',
            'POST'   => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default  => 'find',
        };
    }

    public function handle(Request $request, Closure $next)
    {
        $rawToken = $request->bearerToken();

        if (!$rawToken) {
            return response()->json([
                'data'  => null,
                'error' => ['status' => 401, 'name' => 'UnauthorizedError', 'message' => 'Missing authentication token', 'details' => []],
            ], 401);
        }

        $token = ApiToken::findByRawToken($rawToken);

        if (!$token || $token->isExpired()) {
            return response()->json([
                'data'  => null,
                'error' => ['status' => 401, 'name' => 'UnauthorizedError', 'message' => 'Invalid or expired token', 'details' => []],
            ], 401);
        }

        $slug   = $request->route('slug') ?? 'upload';
        $action = $this->resolveAction($request, $slug);

        if (!$token->can($action, $slug)) {
            return response()->json([
                'data'  => null,
                'error' => ['status' => 403, 'name' => 'ForbiddenError', 'message' => 'Insufficient permissions', 'details' => []],
            ], 403);
        }

        $token->update(['last_used_at' => now()]);
        $request->attributes->set('api_token', $token);

        return $next($request);
    }
}
