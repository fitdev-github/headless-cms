<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function global()
    {
        $settings = Setting::allKeyed();
        return view('admin.settings.global', compact('settings'));
    }

    public function updateGlobal(Request $request)
    {
        $data = $request->validate([
            'site_name'    => ['required', 'string', 'max:255'],
            'app_url'      => ['required', 'url'],
            'cors_origins' => ['required', 'string'],
            'timezone'     => ['required', 'string', 'timezone'],
        ]);

        Setting::setMany($data);

        return back()->with('success', 'Settings saved.');
    }

    public function users()
    {
        $users = User::orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.form', ['user' => null]);
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
            'role'     => ['required', 'in:superadmin,editor'],
        ]);

        User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.users')->with('success', 'User created.');
    }

    public function editUser(int $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.form', compact('user'));
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email,'.$id],
            'role'      => ['required', 'in:superadmin,editor'],
            'is_active' => ['boolean'],
            'password'  => ['nullable', Password::min(8)],
        ]);

        $updateData = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => $request->boolean('is_active', $user->is_active),
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users')->with('success', 'User updated.');
    }

    public function destroyUser(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', 'User deleted.');
    }

    // ─── i18n Locale Management ────────────────────────────────────────────────

    public function addLocale(Request $request)
    {
        $request->validate(['locale' => ['required', 'string', 'regex:/^[a-z]{2,5}(-[a-z]{2,4})?$/i', 'max:10']]);

        $locale  = strtolower(trim($request->locale));
        $locales = json_decode(Setting::get('locales', '["en"]'), true) ?? ['en'];

        if (!in_array($locale, $locales)) {
            $locales[] = $locale;
            Setting::set('locales', json_encode(array_values($locales)));
        }

        return back()->with('success', "Locale '{$locale}' added.");
    }

    public function removeLocale(Request $request)
    {
        $request->validate(['locale' => ['required', 'string']]);

        $locale  = $request->locale;
        $default = Setting::get('default_locale', 'en');

        if ($locale === $default) {
            return back()->with('error', 'Cannot remove the default locale. Set a new default first.');
        }

        $locales = json_decode(Setting::get('locales', '["en"]'), true) ?? ['en'];
        $locales = array_values(array_filter($locales, fn ($l) => $l !== $locale));
        Setting::set('locales', json_encode($locales));

        return back()->with('success', "Locale '{$locale}' removed.");
    }

    public function setDefaultLocale(Request $request)
    {
        $request->validate(['locale' => ['required', 'string']]);

        $locale  = $request->locale;
        $locales = json_decode(Setting::get('locales', '["en"]'), true) ?? ['en'];

        if (!in_array($locale, $locales)) {
            return back()->with('error', "Locale '{$locale}' is not in the available locales list.");
        }

        Setting::set('default_locale', $locale);

        return back()->with('success', "Default locale set to '{$locale}'.");
    }
}
