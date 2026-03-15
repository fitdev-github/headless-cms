<?php

namespace App\Http\Middleware;

use App\Models\ApiRole;
use App\Models\ApiToken;
use App\Models\ApiUser;
use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    public function __construct(protected JwtService $jwt) {}

    private function resolveAction(Request $request, string $slug): string
    {
        $method = $request->method();
        $id     = $request->route('id');

        if ($slug === 'upload') {
            return match ($method) {
                'GET'    => $id ? 'upload.findOne' : 'upload.find',
                'POST'   => 'upload.upload',
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

    private function forbidden(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data'  => null,
            'error' => ['status' => 403, 'name' => 'ForbiddenError', 'message' => 'Insufficient permissions', 'details' => []],
        ], 403);
    }

    private function unauthorized(string $msg = 'Missing or invalid authentication token'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data'  => null,
            'error' => ['status' => 401, 'name' => 'UnauthorizedError', 'message' => $msg, 'details' => []],
        ], 401);
    }

    public function handle(Request $request, Closure $next)
    {
        $rawToken = $request->bearerToken();
        $slug     = $request->route('slug') ?? 'upload';
        $action   = $this->resolveAction($request, $slug);

        // ── 1. JWT (api_user) ─────────────────────────────────────────────────
        if ($rawToken && $this->jwt->isJwt($rawToken)) {
            try {
                $payload = $this->jwt->decode($rawToken);
            } catch (\RuntimeException $e) {
                return $this->unauthorized('Invalid or expired JWT: ' . $e->getMessage());
            }

            $user = ApiUser::with('role')->find($payload['sub'] ?? null);

            if (!$user || $user->blocked) {
                return $this->unauthorized('User not found or blocked.');
            }

            $role = $user->role;
            if (!$role || !$role->can($action, $slug)) {
                return $this->forbidden();
            }

            $request->attributes->set('api_user', $user);
            return $next($request);
        }

        // ── 2. Bearer API token (existing hashed tokens) ──────────────────────
        if ($rawToken) {
            $token = ApiToken::findByRawToken($rawToken);

            if (!$token || $token->isExpired()) {
                return $this->unauthorized('Invalid or expired token.');
            }

            if (!$token->can($action, $slug)) {
                return $this->forbidden();
            }

            $token->update(['last_used_at' => now()]);
            $request->attributes->set('api_token', $token);
            return $next($request);
        }

        // ── 3. No token → check Public role permissions ────────────────────────
        $publicRole = ApiRole::public();

        if ($publicRole && $publicRole->can($action, $slug)) {
            return $next($request);
        }

        return $this->unauthorized('Missing authentication token');
    }
}
