@extends('layouts.admin')
@section('title', 'Content-Type Builder')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Content-Type Builder</h1>
            <p class="text-sm text-gray-500 mt-1">Build your content architecture</p>
        </div>
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create new type
            </button>
            <div x-show="open" @click.outside="open = false" x-cloak
                class="absolute right-0 mt-1 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                <a href="{{ route('admin.ctb.create', 'collection') }}"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                    <span class="text-lg mt-0.5">📦</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Collection Type</p>
                        <p class="text-xs text-gray-400">Multiple entries (articles, products…)</p>
                    </div>
                </a>
                <a href="{{ route('admin.ctb.create', 'single') }}"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                    <span class="text-lg mt-0.5">📄</span>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Single Type</p>
                        <p class="text-xs text-gray-400">One-off content (homepage, about…)</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Collection Types --}}
    <div class="mb-6">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Collection Types</h2>
        @if($collections->isEmpty())
            <div class="bg-white border border-dashed border-gray-200 rounded-xl p-8 text-center">
                <p class="text-sm text-gray-400 mb-3">No collection types yet.</p>
                <a href="{{ route('admin.ctb.create', 'collection') }}" class="text-sm text-blue-600 font-medium hover:underline">
                    Create your first collection type →
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($collections as $ct)
                <div class="bg-white border border-gray-100 rounded-xl p-4 hover:border-blue-200 transition-colors">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">{{ $ct->icon ?: '📦' }}</span>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $ct->display_name }}</p>
                                <p class="text-xs text-gray-400">/api/v1/{{ $ct->plural_name }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ $ct->fields()->count() }} fields</span>
                    </div>
                    @if($ct->description)
                        <p class="text-xs text-gray-500 mb-3">{{ $ct->description }}</p>
                    @endif
                    <div class="flex gap-2">
                        <a href="{{ route('admin.ctb.edit', $ct->id) }}"
                            class="flex-1 text-center text-xs font-medium py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors">
                            Edit
                        </a>
                        <a href="{{ route('admin.cm.index', $ct->plural_name) }}"
                            class="flex-1 text-center text-xs font-medium py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg transition-colors">
                            View entries
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Single Types --}}
    <div>
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Single Types</h2>
        @if($singles->isEmpty())
            <div class="bg-white border border-dashed border-gray-200 rounded-xl p-8 text-center">
                <p class="text-sm text-gray-400 mb-3">No single types yet.</p>
                <a href="{{ route('admin.ctb.create', 'single') }}" class="text-sm text-blue-600 font-medium hover:underline">
                    Create your first single type →
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($singles as $ct)
                <div class="bg-white border border-gray-100 rounded-xl p-4 hover:border-blue-200 transition-colors">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">{{ $ct->icon ?: '📄' }}</span>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $ct->display_name }}</p>
                                <p class="text-xs text-gray-400">/api/v1/{{ $ct->singular_name }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.ctb.edit', $ct->id) }}"
                            class="flex-1 text-center text-xs font-medium py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg transition-colors">
                            Edit
                        </a>
                        <a href="{{ route('admin.cm.index', $ct->singular_name) }}"
                            class="flex-1 text-center text-xs font-medium py-1.5 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-lg transition-colors">
                            Edit entry
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
