@extends('layouts.admin')
@section('title', 'Webhook Logs — '.$webhook->name)

@section('content')
<div class="p-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.webhooks.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Webhooks</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm text-gray-900 font-medium">{{ $webhook->name }} — Delivery Logs</span>
    </div>

    @if($logs->isEmpty())
        <div class="bg-white border border-dashed border-gray-200 rounded-xl py-12 text-center">
            <p class="text-sm text-gray-400">No deliveries yet. Trigger a CMS event to see logs here.</p>
        </div>
    @else
    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">HTTP</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Response</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $log->success ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            <span class="text-xs font-medium {{ $log->success ? 'text-green-700' : 'text-red-600' }}">
                                {{ $log->success ? 'Success' : 'Failed' }}
                            </span>
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-gray-600">{{ $log->event }}</td>
                    <td class="px-5 py-3 text-sm">
                        <span class="font-mono {{ $log->status_code && $log->status_code < 300 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $log->status_code ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400 max-w-xs truncate">{{ $log->response }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400">{{ $log->delivered_at?->diffForHumans() }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
    @endif
</div>
@endsection
