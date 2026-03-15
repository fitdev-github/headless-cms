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

    @if(session('success'))
    <div class="p-3 bg-green-50 border border-green-200 rounded-lg mb-4 text-sm text-green-700">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
        {{ session('error') }}
    </div>
    @endif

    {{-- General Settings --}}
    <div class="bg-white border border-gray-100 rounded-xl p-6 mb-6">
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

    {{-- Internationalization (i18n) --}}
    <div class="bg-white border border-gray-100 rounded-xl p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-lg">🌐</span>
            <h2 class="text-sm font-semibold text-gray-900">Internationalization</h2>
        </div>

        @php
            $locales       = json_decode(\App\Models\Setting::get('locales', '["en"]'), true) ?? ['en'];
            $defaultLocale = \App\Models\Setting::get('default_locale', 'en');
        @endphp

        {{-- Locale list --}}
        <div class="mb-5">
            <p class="text-xs font-medium text-gray-600 mb-2">Available Locales</p>
            <div class="space-y-2">
                @foreach($locales as $loc)
                <div class="flex items-center justify-between p-2.5 bg-gray-50 border border-gray-100 rounded-lg">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700 rounded uppercase">{{ $loc }}</span>
                        @if($loc === $defaultLocale)
                            <span class="text-xs text-green-600 font-medium flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Default
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if($loc !== $defaultLocale)
                            <form method="POST" action="{{ route('admin.settings.locale.set-default') }}" class="inline">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $loc }}">
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                    Set default
                                </button>
                            </form>
                            <span class="text-gray-300">|</span>
                            <form method="POST" action="{{ route('admin.settings.locale.remove') }}" class="inline"
                                onsubmit="return confirm('Remove locale {{ $loc }}? Entries remain but this locale will no longer appear in filters.')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="locale" value="{{ $loc }}">
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">
                                    Remove
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400 italic">cannot remove default</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Add locale --}}
        <div class="border-t border-gray-100 pt-4">
            <p class="text-xs font-medium text-gray-600 mb-2">Add Locale</p>
            <form method="POST" action="{{ route('admin.settings.locale.add') }}" class="flex items-center gap-2">
                @csrf
                <input type="text" name="locale" placeholder="e.g. th, fr, ja, zh-tw" maxlength="10"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 font-mono"
                    pattern="[a-zA-Z]{2,5}(-[a-zA-Z]{2,4})?">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors whitespace-nowrap">
                    + Add Locale
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-1.5">Use ISO 639-1 codes: <code>en</code>, <code>th</code>, <code>fr</code>, <code>de</code>, <code>ja</code>, <code>zh-tw</code></p>
        </div>
    </div>
</div>
@endsection
