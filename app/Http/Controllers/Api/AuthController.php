<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiRole;
use App\Models\ApiUser;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(protected JwtService $jwt) {}

    /** POST /api/auth/local */
    public function login(Request $request)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],   // email or username
            'password'   => ['required', 'string'],
        ]);

        $user = ApiUser::where('email', $data['identifier'])
                       ->orWhere('username', $data['identifier'])
                       ->first();

        if (!$user || !$user->checkPassword($data['password'])) {
            return response()->json(['error' => [
                'status'  => 400,
                'name'    => 'ValidationError',
                'message' => 'Invalid identifier or password.',
            ]], 400);
        }

        if ($user->blocked) {
            return response()->json(['error' => [
                'status'  => 403,
                'name'    => 'ForbiddenError',
                'message' => 'Your account has been blocked.',
            ]], 403);
        }

        $token = $this->jwt->encode(['sub' => $user->id, 'email' => $user->email, 'role' => $user->role?->name]);

        return response()->json([
            'jwt'  => $token,
            'user' => $user->load('role')->toPublicArray(),
        ]);
    }

    /** POST /api/auth/local/register */
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:api_users'],
            'email'    => ['required', 'email', 'unique:api_users'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $role = ApiRole::authenticated() ?? ApiRole::public();

        $user = ApiUser::create([
            'username'  => $data['username'],
            'email'     => $data['email'],
            'password'  => $data['password'],
            'confirmed' => false,
            'role_id'   => $role?->id,
        ]);

        $token = $this->jwt->encode(['sub' => $user->id, 'email' => $user->email, 'role' => $role?->name]);

        return response()->json([
            'jwt'  => $token,
            'user' => $user->load('role')->toPublicArray(),
        ], 201);
    }

    /** GET /api/users/me — requires JWT */
    public function me(Request $request)
    {
        $apiUser = $request->attributes->get('api_user');
        if (!$apiUser) {
            return response()->json(['error' => ['status' => 401, 'name' => 'UnauthorizedError', 'message' => 'Missing or invalid token.']], 401);
        }
        return response()->json($apiUser->load('role')->toPublicArray());
    }

    /** PUT /api/users/me — requires JWT */
    public function updateMe(Request $request)
    {
        $apiUser = $request->attributes->get('api_user');
        if (!$apiUser) {
            return response()->json(['error' => ['status' => 401, 'name' => 'UnauthorizedError', 'message' => 'Missing or invalid token.']], 401);
        }

        $data = $request->validate([
            'username' => ['sometimes', 'string', 'max:100', "unique:api_users,username,{$apiUser->id}"],
            'email'    => ['sometimes', 'email', "unique:api_users,email,{$apiUser->id}"],
            'password' => ['sometimes', 'string', 'min:6'],
        ]);

        if (isset($data['password'])) {
            $apiUser->password = $data['password']; // triggers hash via mutator
            unset($data['password']);
            $apiUser->save();
        }

        if (!empty($data)) {
            $apiUser->update($data);
        }

        return response()->json($apiUser->fresh()->load('role')->toPublicArray());
    }
}
