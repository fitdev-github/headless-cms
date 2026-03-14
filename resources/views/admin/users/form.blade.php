@extends('layouts.admin')
@section('title', $user ? 'Edit User' : 'Add User')

@section('content')
<div class="p-6 max-w-lg">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users') }}" class="text-sm text-gray-400 hover:text-gray-600">Users</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm text-gray-900 font-medium">{{ $user ? 'Edit User' : 'Add User' }}</span>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl p-6">
        @if($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
                <ul class="list-disc list-inside"><li>{{ $errors->first() }}</li></ul>
            </div>
        @endif

        <form method="POST" action="{{ $user ? route('admin.users.update', $user->id) : route('admin.users.store') }}">
            @csrf
            @if($user) @method('PUT') @endif

            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Email Address *</label>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Password {{ $user ? '(leave blank to keep current)' : '*' }}
                </label>
                <input type="password" name="password" placeholder="{{ $user ? 'New password…' : 'Min. 8 characters' }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-3">
                <label class="block text-xs font-medium text-gray-600 mb-1">Role *</label>
                <select name="role"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="editor" {{ old('role', $user->role ?? '') === 'editor' ? 'selected' : '' }}>Editor</option>
                    <option value="superadmin" {{ old('role', $user->role ?? '') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                </select>
            </div>

            @if($user)
            <div class="mb-4 flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_active" class="text-xs text-gray-600">Active (can log in)</label>
            </div>
            @endif

            <div class="flex gap-2 mt-5">
                <a href="{{ route('admin.users') }}"
                    class="flex-1 py-2.5 text-sm font-medium text-center text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="flex-1 py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    {{ $user ? 'Save Changes' : 'Create User' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
