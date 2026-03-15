@extends('layouts.admin')
@section('title', 'Roles — Users & Permissions')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users & Permissions</h1>
            <p class="text-sm text-gray-500 mt-1">Control what API users and public visitors can access</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-5 border-b border-gray-200">
        <a href="{{ route('admin.up.roles') }}"
            class="px-4 py-2 text-sm font-medium border-b-2 border-blue-600 text-blue-600 -mb-px">Roles</a>
        <a href="{{ route('admin.up.users') }}"
            class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">API Users</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($roles as $role)
        <div class="bg-white border border-gray-100 rounded-xl p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $role->name }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $role->description ?: '—' }}</p>
                </div>
                @if($role->is_default)
                <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium">Default</span>
                @endif
            </div>
            <p class="text-xs text-gray-500">{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}</p>
            <a href="{{ route('admin.up.roles.edit', $role->id) }}"
                class="mt-auto text-sm font-medium text-blue-600 hover:underline">
                Edit permissions →
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
