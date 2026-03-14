@extends('layouts.setup', ['currentStep' => 4])
@section('title', 'Site Settings')
@section('subtitle', 'Step 4 of 5 — Configure Your Site')

@section('content')
<h3 class="text-xl font-semibold text-gray-900 mb-1">Site Settings</h3>
<p class="text-sm text-gray-500 mb-6">These can be changed later in the Settings panel.</p>

@if($errors->any())
    <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('setup.site-settings.save') }}">
    @csrf

    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">Site Name</label>
        <input type="text" name="site_name" value="{{ old('site_name', 'My CMS') }}"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">App URL</label>
        <input type="url" name="app_url" value="{{ old('app_url', request()->root()) }}" placeholder="https://example.com"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <p class="text-xs text-gray-400 mt-1">Used for API responses and media URLs.</p>
    </div>

    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">Timezone</label>
        <select name="timezone"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            @foreach(timezone_identifiers_list() as $tz)
                <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-5">
        <label class="block text-xs font-medium text-gray-600 mb-1">CORS Origins</label>
        <input type="text" name="cors_origins" value="{{ old('cors_origins', '*') }}"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <p class="text-xs text-gray-400 mt-1">Comma-separated origins allowed to access the API. Use <code>*</code> for all.</p>
    </div>

    <button type="submit"
        class="block w-full py-2.5 px-4 text-center font-medium rounded-lg transition-colors text-sm bg-blue-600 hover:bg-blue-700 text-white">
        Finish Setup →
    </button>
</form>
@endsection
