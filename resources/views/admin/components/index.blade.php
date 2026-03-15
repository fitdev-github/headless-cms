@extends('layouts.admin')
@section('title', 'Components')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Components</h1>
            <p class="text-sm text-gray-400 mt-0.5">Reusable field groups you can embed in content types.</p>
        </div>
        <a href="{{ route('admin.components.create') }}"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New component
        </a>
    </div>

    @if($components->isEmpty())
        <div class="bg-white border border-dashed border-gray-200 rounded-xl p-12 text-center">
            <div class="text-4xl mb-3">🧩</div>
            <p class="text-sm font-medium text-gray-900 mb-1">No components yet</p>
            <p class="text-sm text-gray-400 mb-4">Components are reusable field groups (e.g. SEO, Hero, Slider).</p>
            <a href="{{ route('admin.components.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                Create your first component →
            </a>
        </div>
    @else
        @php
            $grouped = $components->groupBy('category');
        @endphp

        @foreach($grouped as $category => $group)
        <div class="mb-6">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">
                {{ $category ?: 'default' }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($group as $component)
                <div class="bg-white border border-gray-100 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
                        {{ $component->icon ?: '🧩' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $component->display_name }}</p>
                        <p class="text-xs text-gray-400 font-mono truncate">{{ $component->name }}</p>
                        <p class="text-xs text-gray-400">{{ $component->fields_count }} field{{ $component->fields_count !== 1 ? 's' : '' }}</p>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <a href="{{ route('admin.components.edit', $component->id) }}"
                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form method="POST" action="{{ route('admin.components.destroy', $component->id) }}"
                            onsubmit="return confirm('Delete component {{ e($component->display_name) }}? This will break any fields using it.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection
