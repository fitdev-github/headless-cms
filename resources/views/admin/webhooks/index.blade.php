@extends('layouts.admin')
@section('title', 'Webhooks')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Webhooks</h1>
            <p class="text-sm text-gray-500 mt-1">Trigger HTTP POST requests on CMS events</p>
        </div>
        <a href="{{ route('admin.webhooks.create') }}"
            class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create webhook
        </a>
    </div>

    @if($webhooks->isEmpty())
        <div class="bg-white border border-dashed border-gray-200 rounded-xl py-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-sm text-gray-400 mb-3">No webhooks yet.</p>
            <a href="{{ route('admin.webhooks.create') }}" class="text-sm text-blue-600 font-medium hover:underline">Create your first webhook →</a>
        </div>
    @else
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Delivery</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enabled</th>
                    <th class="relative px-5 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($webhooks as $wh)
                @php $lastLog = $wh->latestLog; @endphp
                <tr class="hover:bg-gray-50" x-data="{ enabled: {{ $wh->enabled ? 'true' : 'false' }} }">
                    <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $wh->name }}</td>
                    <td class="px-5 py-3 text-sm text-gray-500 font-mono truncate max-w-xs">{{ $wh->url }}</td>
                    <td class="px-5 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($wh->events ?? [] as $ev)
                            <span class="text-xs px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded font-mono">{{ $ev }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-5 py-3 text-sm">
                        @if($lastLog)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ $lastLog->success ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                <span class="text-gray-400 text-xs">{{ $lastLog->status_code ?? '—' }}</span>
                                <span class="text-gray-400 text-xs">{{ $lastLog->delivered_at->diffForHumans() }}</span>
                            </span>
                        @else
                            <span class="text-gray-300 text-xs">Never triggered</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <button
                            @click="enabled = !enabled; fetch('{{ route('admin.webhooks.toggle', $wh->id) }}', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })"
                            :class="enabled ? 'bg-blue-600' : 'bg-gray-200'"
                            class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 focus:outline-none">
                            <span :class="enabled ? 'translate-x-4' : 'translate-x-0'" class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ring-1 ring-gray-200"></span>
                        </button>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.webhooks.logs', $wh->id) }}"
                                class="text-xs px-3 py-1.5 font-medium text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
                                Logs
                            </a>
                            <a href="{{ route('admin.webhooks.edit', $wh->id) }}"
                                class="text-xs px-3 py-1.5 font-medium text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.webhooks.destroy', $wh->id) }}"
                                onsubmit="return confirm('Delete this webhook?')">
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
    </div>

    {{-- Info card --}}
    <div class="mt-6 bg-gray-900 rounded-xl p-5 text-sm text-gray-300">
        <p class="text-white font-semibold mb-2">Available Events</p>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-1 font-mono text-xs">
            @foreach(['entry.create','entry.update','entry.publish','entry.unpublish','entry.delete','media.create','media.delete'] as $ev)
            <span class="text-blue-400">{{ $ev }}</span>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
