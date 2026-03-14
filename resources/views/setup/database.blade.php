@extends('layouts.setup', ['currentStep' => 2])
@section('title', 'Database Setup')
@section('subtitle', 'Step 2 of 5 — Database Configuration')

@section('content')
<h3 class="text-xl font-semibold text-gray-900 mb-1">Database Configuration</h3>
<p class="text-sm text-gray-500 mb-6">Enter your database credentials to continue.</p>

@if(session('error'))
    <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

@if(session('env_content'))
<div x-data="{ show: false }" class="mb-5">
    <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800 mb-2">
        <strong>Your .env file is not writable.</strong> Copy the content below and save it manually as <code>.env</code> in the project root, then click "I've saved it".
    </div>
    <button @click="show = !show" class="text-sm text-blue-600 underline mb-2">Toggle .env preview</button>
    <div x-show="show" class="relative">
        <textarea id="env-content" class="w-full h-48 font-mono text-xs p-3 bg-gray-900 text-green-400 rounded-lg border border-gray-700" readonly>{{ session('env_content') }}</textarea>
        <button onclick="document.getElementById('env-content').select();document.execCommand('copy');this.textContent='Copied!';"
            class="absolute top-2 right-2 text-xs bg-gray-700 text-white px-2 py-1 rounded hover:bg-gray-600">Copy</button>
    </div>
    <form method="POST" action="{{ route('setup.database.save') }}" class="mt-3">
        @csrf
        <input type="hidden" name="manual_env" value="1">
        <button type="submit" class="w-full py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            I've saved the .env file — Continue →
        </button>
    </form>
</div>
@else

<div x-data="{
    testing: false,
    testStatus: null,
    testMessage: '',
    async testConnection() {
        this.testing = true;
        this.testStatus = null;
        const data = new FormData(document.getElementById('db-form'));
        const params = new URLSearchParams();
        params.append('_token', data.get('_token'));
        params.append('db_host', data.get('db_host'));
        params.append('db_port', data.get('db_port'));
        params.append('db_name', data.get('db_name'));
        params.append('db_user', data.get('db_user'));
        params.append('db_pass', data.get('db_pass'));
        try {
            const res = await fetch('{{ route('setup.database.test') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: params.toString()
            });
            const json = await res.json();
            this.testStatus = json.ok ? 'success' : 'error';
            this.testMessage = json.message;
        } catch(e) {
            this.testStatus = 'error';
            this.testMessage = 'Request failed.';
        }
        this.testing = false;
    }
}">

<form id="db-form" method="POST" action="{{ route('setup.database.save') }}">
    @csrf

    <div class="grid grid-cols-3 gap-3 mb-3">
        <div class="col-span-2">
            <label class="block text-xs font-medium text-gray-600 mb-1">Database Host</label>
            <input type="text" name="db_host" value="{{ old('db_host', 'localhost') }}"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Port</label>
            <input type="text" name="db_port" value="{{ old('db_port', '3306') }}"
                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
    </div>

    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">Database Name</label>
        <input type="text" name="db_name" value="{{ old('db_name') }}" placeholder="headless_cms"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div class="mb-3">
        <label class="block text-xs font-medium text-gray-600 mb-1">Database Username</label>
        <input type="text" name="db_user" value="{{ old('db_user') }}" placeholder="root"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    <div class="mb-4">
        <label class="block text-xs font-medium text-gray-600 mb-1">Database Password</label>
        <input type="password" name="db_pass" value="{{ old('db_pass') }}"
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>

    {{-- Test connection result --}}
    <div x-show="testStatus === 'success'" class="p-2.5 bg-green-50 border border-green-200 rounded-lg mb-3 text-sm text-green-700 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="testMessage"></span>
    </div>
    <div x-show="testStatus === 'error'" class="p-2.5 bg-red-50 border border-red-200 rounded-lg mb-3 text-sm text-red-700 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span x-text="testMessage"></span>
    </div>

    <div class="flex gap-2">
        <button type="button" @click="testConnection()"
            :disabled="testing"
            class="flex-1 py-2.5 px-4 text-sm font-medium border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors disabled:opacity-50">
            <span x-show="!testing">Test Connection</span>
            <span x-show="testing">Testing…</span>
        </button>
        <button type="submit"
            class="flex-1 py-2.5 px-4 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            Save &amp; Continue →
        </button>
    </div>
</form>
</div>
@endif
@endsection
