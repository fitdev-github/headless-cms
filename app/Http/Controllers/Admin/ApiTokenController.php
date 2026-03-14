<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = ApiToken::orderByDesc('created_at')->get();
        return view('admin.tokens.index', compact('tokens'));
    }

    public function create()
    {
        return view('admin.tokens.form', ['token' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'type'          => ['required', 'in:full-access,read-only,custom'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $rawToken = Str::random(60);

        ApiToken::create([
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'type'          => $data['type'],
            'token_hash'    => hash('sha256', $rawToken),
            'abilities'     => $data['type'] === 'custom' ? $this->buildAbilities($request) : null,
            'duration_days' => $data['duration_days'] ?? null,
            'expires_at'    => isset($data['duration_days'])
                ? now()->addDays($data['duration_days'])
                : null,
        ]);

        return redirect()->route('admin.tokens.index')
            ->with('new_token', $rawToken)
            ->with('success', 'API token created.');
    }

    public function destroy(int $id)
    {
        ApiToken::findOrFail($id)->delete();
        return back()->with('success', 'Token deleted.');
    }

    private function buildAbilities(Request $request): array
    {
        $abilities = [];
        foreach ($request->input('abilities', []) as $action) {
            $abilities['*'][] = $action;
        }
        return $abilities;
    }
}
