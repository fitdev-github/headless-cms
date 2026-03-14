@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="p-6">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Content Types</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['content_types'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Entries</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['entries'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Media Files</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['media'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">API Tokens</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['api_tokens'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Content Types --}}
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900 text-sm">Content Types</h2>
                <a href="{{ route('admin.ctb.index') }}" class="text-xs text-blue-600 hover:underline">Manage →</a>
            </div>
            @if($contentTypes->isEmpty())
                <div class="px-5 py-8 text-center">
                    <p class="text-sm text-gray-400 mb-3">No content types yet.</p>
                    <a href="{{ route('admin.ctb.create', 'collection') }}" class="text-sm text-blue-600 hover:underline font-medium">Create your first content type →</a>
                </div>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach($contentTypes as $ct)
                    <li class="flex items-center justify-between px-5 py-3">
                        <div class="flex items-center gap-2">
                            <span class="text-base">{{ $ct->icon ?: '📄' }}</span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $ct->display_name }}</p>
                                <p class="text-xs text-gray-400">/api/v1/{{ $ct->plural_name }} · {{ $ct->type }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $ct->entries()->count() }} entries</span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Recent Entries --}}
        <div class="bg-white rounded-xl border border-gray-100">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900 text-sm">Recent Entries</h2>
            </div>
            @if($recentEntries->isEmpty())
                <div class="px-5 py-8 text-center">
                    <p class="text-sm text-gray-400">No entries yet.</p>
                </div>
            @else
                <ul class="divide-y divide-gray-50">
                    @foreach($recentEntries as $entry)
                    <li class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $entry->contentType->display_name }}</p>
                            <p class="text-xs text-gray-400">{{ $entry->created_at->diffForHumans() }} · by {{ $entry->creator->name ?? 'Unknown' }}</p>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $entry->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $entry->status }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
@endsection
