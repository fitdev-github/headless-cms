@extends('layouts.admin')
@section('title', 'Global Settings')

@section('content')
<div class="p-6 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Global application configuration</p>
    </div>

    {{-- Settings Nav --}}
    <div class="flex gap-1 mb-6 border-b border-gray-200">
        <a href="{{ route('admin.settings.global') }}"
            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors {{ request()->routeIs('admin.settings.global') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Global
        </a>
        <a href="{{ route('admin.tokens.index') }}"
            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-gray-500 hover:text-gray-700">
            API Tokens
        </a>
        <a href="{{ route('admin.users') }}"
            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-gray-500 hover:text-gray-700">
            Users
        </a>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl p-6">
        @if($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
                <ul class="list-disc list-inside"><li>{{ $errors->first() }}</li></ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.global.update') }}">
            @csrf @method('PUT')

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Site Name</label>
                <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">App URL</label>
                <input type="url" name="app_url" value="{{ old('app_url', $settings['app_url'] ?? config('app.url')) }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Used for media URLs in API responses.</p>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">CORS Origins</label>
                <input type="text" name="cors_origins" value="{{ old('cors_origins', $settings['cors_origins'] ?? '*') }}"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-400 mt-1">Comma-separated. Use <code>*</code> to allow all origins.</p>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-medium text-gray-600 mb-1">Timezone</label>
                <select name="timezone"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" {{ old('timezone', $settings['timezone'] ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                class="px-5 py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Save Changes
            </button>
        </form>
    </div>
</div>
@endsection
