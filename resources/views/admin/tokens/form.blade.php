@extends('layouts.admin')
@section('title', 'Create API Token')

@section('content')
<div class="p-6 max-w-xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.tokens.index') }}" class="text-sm text-gray-400 hover:text-gray-600">API Tokens</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm text-gray-900 font-medium">Create Token</span>
    </div>

    <div class="bg-white border border-gray-100 rounded-xl p-6">
        @if($errors->any())
            <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
                <ul class="list-disc list-inside"><li>{{ $errors->first() }}</li></ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.tokens.store') }}"
            x-data="{ tokenType: 'read-only' }">
            @csrf

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Token Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Next.js Frontend"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <input type="text" name="description" value="{{ old('description') }}" placeholder="Optional description"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 mb-2">Token Type *</label>
                <div class="space-y-2">
                    @foreach([
                        ['read-only',   'Read-only',   'Can only fetch data (GET requests).'],
                        ['full-access', 'Full access', 'Can create, update and delete entries.'],
                        ['custom',      'Custom',      'Choose specific permissions below.'],
                    ] as [$val, $label, $desc])
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition-colors"
                        :class="tokenType === '{{ $val }}' ? 'border-blue-400 bg-blue-50' : 'border-gray-200 hover:bg-gray-50'">
                        <input type="radio" name="type" value="{{ $val }}" x-model="tokenType"
                            class="mt-0.5 text-blue-600 focus:ring-blue-500">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $label }}</p>
                            <p class="text-xs text-gray-400">{{ $desc }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Custom permissions --}}
            <div x-show="tokenType === 'custom'" class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                <p class="text-xs font-semibold text-orange-800 mb-2">Select allowed actions</p>
                <div class="grid grid-cols-2 gap-1">
                    @foreach(['find', 'findOne', 'create', 'update', 'delete', 'upload.find', 'upload.findOne', 'upload.upload', 'upload.delete'] as $action)
                    <label class="flex items-center gap-2 text-xs text-orange-700 cursor-pointer">
                        <input type="checkbox" name="abilities[]" value="{{ $action }}"
                            class="rounded border-orange-300 text-orange-600 focus:ring-orange-500">
                        {{ $action }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-xs font-medium text-gray-600 mb-1">Token Expiry (days)</label>
                <input type="number" name="duration_days" value="{{ old('duration_days') }}" min="1" placeholder="Leave blank for no expiry"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex gap-2">
                <a href="{{ route('admin.tokens.index') }}"
                    class="flex-1 py-2.5 text-sm font-medium text-center text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="flex-1 py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Generate Token
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
