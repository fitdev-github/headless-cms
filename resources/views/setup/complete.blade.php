@extends('layouts.setup', ['currentStep' => 5])
@section('title', 'Installation Complete')
@section('subtitle', 'Step 5 of 5 — You\'re all set!')

@section('content')
<div class="text-center py-4">
    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
    </div>

    <h3 class="text-xl font-semibold text-gray-900 mb-2">Installation Complete!</h3>
    <p class="text-sm text-gray-500 mb-6">HeadlessCMS has been successfully installed. You can now log in to your admin panel.</p>

    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left space-y-2">
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">Admin Panel</span>
            <a href="{{ url('/admin') }}" class="text-blue-600 font-medium hover:underline">/admin</a>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">API Base URL</span>
            <code class="text-xs bg-gray-200 px-2 py-0.5 rounded text-gray-700">{{ url('/api/v1') }}</code>
        </div>
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">API Docs</span>
            <a href="{{ url('/admin/settings/api-tokens') }}" class="text-blue-600 text-xs hover:underline">Settings → API Tokens</a>
        </div>
    </div>

    <a href="{{ url('/admin') }}"
        class="block w-full py-2.5 px-4 text-center font-medium rounded-lg transition-colors text-sm bg-blue-600 hover:bg-blue-700 text-white">
        Go to Admin Panel →
    </a>
</div>
@endsection
