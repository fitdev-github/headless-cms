@extends('layouts.setup', ['currentStep' => 1])
@section('title', 'Welcome')
@section('subtitle', 'Step 1 of 5 — Requirements Check')

@section('content')
<h3 class="text-xl font-semibold text-gray-900 mb-1">Welcome to HeadlessCMS</h3>
<p class="text-sm text-gray-500 mb-6">Let's make sure your server meets all requirements before we begin.</p>

<div class="space-y-2 mb-6">
    @foreach($requirements as $req)
    <div class="flex items-center justify-between py-2.5 px-3 rounded-lg
        {{ $req['ok'] ? 'bg-green-50' : ($req['required'] ? 'bg-red-50' : 'bg-yellow-50') }}">
        <div class="flex items-center gap-2">
            @if($req['ok'])
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-green-800">{{ $req['name'] }}</span>
            @elseif($req['required'])
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-red-800 font-medium">{{ $req['name'] }}</span>
            @else
                <svg class="w-5 h-5 text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-yellow-800">{{ $req['name'] }}</span>
            @endif
        </div>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full
            {{ $req['ok'] ? 'bg-green-100 text-green-700' : ($req['required'] ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
            {{ $req['ok'] ? 'PASS' : ($req['required'] ? 'FAIL' : 'OPTIONAL') }}
        </span>
    </div>
    @endforeach
</div>

@if(!$allPass)
    <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-5 text-sm text-red-700">
        Please fix the required issues above before continuing.
    </div>
@endif

<a href="{{ $allPass ? route('setup.database') : '#' }}"
   class="block w-full py-2.5 px-4 text-center font-medium rounded-lg transition-colors text-sm
          {{ $allPass ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }}">
    Continue to Database Setup →
</a>
@endsection
