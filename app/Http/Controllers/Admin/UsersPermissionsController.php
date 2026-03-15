<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiPermission;
use App\Models\ApiRole;
use App\Models\ApiUser;
use App\Models\ContentType;
use Illuminate\Http\Request;

class UsersPermissionsController extends Controller
{
    // ── Roles ─────────────────────────────────────────────────────────────────

    public function roles()
    {
        $roles = ApiRole::withCount('users')->get();
        return view('admin.users-permissions.roles.index', compact('roles'));
    }

    public function editRole(int $id)
    {
        $role         = ApiRole::with('permissions')->findOrFail($id);
        $contentTypes = ContentType::orderBy('display_name')->get();
        $actions      = ['find', 'findOne', 'create', 'update', 'delete'];
        $uploadActions = ['upload.find', 'upload.findOne', 'upload.upload', 'upload.delete'];

        // Build permission map: [subject][action] => bool
        $permissions = [];
        foreach ($role->permissions as $p) {
            $permissions[$p->subject][$p->action] = $p->enabled;
        }

        return view('admin.users-permissions.roles.edit', compact(
            'role', 'contentTypes', 'actions', 'uploadActions', 'permissions'
        ));
    }

    public function updateRole(Request $request, int $id)
    {
        $role    = ApiRole::findOrFail($id);
        $enabled = $request->input('permissions', []);

        // enabled is: permissions[subject][action] = '1'
        $contentTypes  = ContentType::pluck('plural_name')->toArray();
        $subjects      = array_merge(['upload'], $contentTypes);
        $actions       = ['find', 'findOne', 'create', 'update', 'delete'];
        $uploadActions = ['upload.find', 'upload.findOne', 'upload.upload', 'upload.delete'];

        foreach ($subjects as $subject) {
            $acts = $subject === 'upload' ? $uploadActions : $actions;
            foreach ($acts as $action) {
                $isEnabled = isset($enabled[$subject][$action]);
                ApiPermission::updateOrCreate(
                    ['role_id' => $role->id, 'subject' => $subject, 'action' => $action],
                    ['enabled' => $isEnabled]
                );
            }
        }

        return back()->with('success', 'Permissions updated for "' . $role->name . '".');
    }

    // ── API Users ─────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = ApiUser::with('role')->orderByDesc('created_at');
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%'.$request->q.'%')
                  ->orWhere('username', 'like', '%'.$request->q.'%');
            });
        }
        $users = $query->paginate(20)->withQueryString();
        return view('admin.users-permissions.users.index', compact('users'));
    }

    public function blockUser(int $id)
    {
        $user = ApiUser::findOrFail($id);
        $user->update(['blocked' => !$user->blocked]);
        return back()->with('success', $user->blocked ? 'User blocked.' : 'User unblocked.');
    }

    public function destroyUser(int $id)
    {
        ApiUser::findOrFail($id)->delete();
        return back()->with('success', 'User deleted.');
    }
}
