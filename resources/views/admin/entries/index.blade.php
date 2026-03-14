@extends('layouts.admin')
@section('title', $ct->display_name)

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xl">{{ $ct->icon ?: '📄' }}</span>
                <h1 class="text-2xl font-bold text-gray-900">{{ $ct->display_name }}</h1>
            </div>
            <p class="text-sm text-gray-500">{{ $entries->total() }} {{ Str::plural('entry', $entries->total()) }}</p>
        </div>
        <a href="{{ route('admin.cm.create', $ct->plural_name) }}"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create new entry
        </a>
    </div>

    {{-- Filters --}}
    @if($ct->draft_publish)
    <div class="flex items-center gap-2 mb-4">
        <a href="{{ route('admin.cm.index', $ct->plural_name) }}"
            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ !request('status') ? 'bg-blue-100 text-blue-700' : 'text-gray-500 hover:bg-gray-100' }}">
            All
        </a>
        <a href="{{ route('admin.cm.index', $ct->plural_name) }}?status=published"
            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ request('status') === 'published' ? 'bg-green-100 text-green-700' : 'text-gray-500 hover:bg-gray-100' }}">
            Published
        </a>
        <a href="{{ route('admin.cm.index', $ct->plural_name) }}?status=draft"
            class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors {{ request('status') === 'draft' ? 'bg-yellow-100 text-yellow-700' : 'text-gray-500 hover:bg-gray-100' }}">
            Draft
        </a>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        @if($entries->isEmpty())
            <div class="px-5 py-16 text-center">
                <p class="text-sm text-gray-400 mb-3">No entries yet.</p>
                <a href="{{ route('admin.cm.create', $ct->plural_name) }}" class="text-sm text-blue-600 font-medium hover:underline">
                    Create your first entry →
                </a>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $titleField ? $titleField->display_name : 'Entry' }}
                        </th>
                        @if($ct->draft_publish)
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        @endif
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                        <th class="relative px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($entries as $entry)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 text-sm text-gray-400 font-mono">{{ $entry->id }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('admin.cm.edit', [$ct->plural_name, $entry->id]) }}"
                                class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                                {{ isset($titleValues[$entry->id]) ? \Illuminate\Support\Str::limit(strip_tags($titleValues[$entry->id]), 60) : 'Entry #'.$entry->id }}
                            </a>
                        </td>
                        @if($ct->draft_publish)
                        <td class="px-5 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $entry->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $entry->status }}
                            </span>
                        </td>
                        @endif
                        <td class="px-5 py-3 text-sm text-gray-400">{{ $entry->updated_at->diffForHumans() }}</td>
                        <td class="px-5 py-3 text-sm text-gray-500">{{ $entry->creator->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.cm.edit', [$ct->plural_name, $entry->id]) }}"
                                    class="text-xs px-3 py-1.5 font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.cm.destroy', [$ct->plural_name, $entry->id]) }}"
                                    onsubmit="return confirm('Delete this entry?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs px-3 py-1.5 font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($entries->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $entries->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
