@extends('layouts.admin')
@section('title', 'API Tokens')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">API Tokens</h1>
            <p class="text-sm text-gray-500 mt-1">Manage access tokens for the REST API</p>
        </div>
        <a href="{{ route('admin.tokens.create') }}"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create new token
        </a>
    </div>

    {{-- Show new token once --}}
    @if(session('new_token'))
    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5">
        <p class="text-sm font-semibold text-green-800 mb-2">⚠️ Copy this token now — it won't be shown again.</p>
        <div class="flex items-center gap-2">
            <code id="raw-token" class="flex-1 text-xs bg-white border border-green-200 rounded-lg px-3 py-2 text-green-900 font-mono break-all">{{ session('new_token') }}</code>
            <button onclick="navigator.clipboard.writeText('{{ session('new_token') }}');this.textContent='Copied!';"
                class="text-xs font-medium px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg whitespace-nowrap">
                Copy
            </button>
        </div>
    </div>
    @endif

    @if($tokens->isEmpty())
        <div class="bg-white border border-dashed border-gray-200 rounded-xl py-16 text-center">
            <p class="text-sm text-gray-400 mb-3">No API tokens yet.</p>
            <a href="{{ route('admin.tokens.create') }}" class="text-sm text-blue-600 font-medium hover:underline">Create your first token →</a>
        </div>
    @else
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Used</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                    <th class="relative px-5 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($tokens as $token)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $token->name }}</p>
                        @if($token->description)
                            <p class="text-xs text-gray-400">{{ $token->description }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $token->type === 'full-access' ? 'bg-purple-100 text-purple-700' : ($token->type === 'read-only' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700') }}">
                            {{ $token->type }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-400">
                        {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                    </td>
                    <td class="px-5 py-3 text-sm">
                        @if($token->expires_at)
                            <span class="{{ $token->isExpired() ? 'text-red-500' : 'text-gray-400' }}">
                                {{ $token->expires_at->format('d M Y') }}
                                @if($token->isExpired()) (expired) @endif
                            </span>
                        @else
                            <span class="text-gray-400">Never</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        <form method="POST" action="{{ route('admin.tokens.destroy', $token->id) }}"
                            onsubmit="return confirm('Revoke this token? Any app using it will lose access.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs px-3 py-1.5 font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                Revoke
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- API reference quick-card --}}
    <div class="mt-6 bg-gray-900 rounded-xl p-5 text-sm text-gray-300">
        <p class="text-white font-semibold mb-3">Quick API Reference</p>
        <div class="space-y-2 font-mono text-xs">
            <div><span class="text-blue-400">GET</span>    {{ url('/api/v1/{slug}') }}</div>
            <div><span class="text-blue-400">GET</span>    {{ url('/api/v1/{slug}/{id}') }}</div>
            <div><span class="text-green-400">POST</span>   {{ url('/api/v1/{slug}') }}</div>
            <div><span class="text-yellow-400">PUT</span>    {{ url('/api/v1/{slug}/{id}') }}</div>
            <div><span class="text-red-400">DELETE</span> {{ url('/api/v1/{slug}/{id}') }}</div>
            <div class="pt-2 border-t border-gray-700 text-gray-400">Authorization: Bearer &lt;token&gt;</div>
        </div>
    </div>
    @endif
</div>
@endsection
