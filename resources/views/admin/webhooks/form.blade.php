@extends('layouts.admin')
@section('title', $webhook ? 'Edit Webhook' : 'Create Webhook')

@section('content')
<div class="p-6 max-w-2xl"
    x-data="{
        headers: {{ $webhook ? json_encode(count($webhook->headers ?? []) ? $webhook->headers : [['key'=>'','value'=>'']]) : json_encode([['key'=>'','value'=>'']]) }},
        addHeader() { this.headers.push({ key: '', value: '' }); },
        removeHeader(i) { this.headers.splice(i, 1); if (!this.headers.length) this.addHeader(); }
    }">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.webhooks.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Webhooks</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm text-gray-900 font-medium">{{ $webhook ? 'Edit' : 'Create' }}</span>
    </div>

    @if($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 rounded-lg mb-4 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
        action="{{ $webhook ? route('admin.webhooks.update', $webhook->id) : route('admin.webhooks.store') }}">
        @csrf
        @if($webhook) @method('PUT') @endif

        <div class="bg-white border border-gray-100 rounded-xl p-6 space-y-5">

            {{-- Name --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $webhook?->name) }}"
                    placeholder="e.g. Next.js Revalidate"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- URL --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">URL *</label>
                <input type="url" name="url" value="{{ old('url', $webhook?->url) }}"
                    placeholder="https://yoursite.com/api/revalidate"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Events --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Events * <span class="text-gray-400 font-normal">(select all that apply)</span></label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach([
                        ['entry.create',    'Entry — Create'],
                        ['entry.update',    'Entry — Update'],
                        ['entry.publish',   'Entry — Publish'],
                        ['entry.unpublish', 'Entry — Unpublish'],
                        ['entry.delete',    'Entry — Delete'],
                        ['media.create',    'Media — Upload'],
                        ['media.delete',    'Media — Delete'],
                    ] as [$val, $label])
                    @php $checked = in_array($val, old('events', $webhook?->events ?? [])); @endphp
                    <label class="flex items-center gap-2 text-sm cursor-pointer p-2 rounded-lg hover:bg-gray-50 border border-gray-100
                        {{ $checked ? 'border-blue-200 bg-blue-50' : '' }}">
                        <input type="checkbox" name="events[]" value="{{ $val }}"
                            {{ $checked ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-gray-700 font-mono text-xs">{{ $val }}</span>
                        <span class="text-gray-400 text-xs ml-auto hidden sm:block">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Custom Headers --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-2">Custom Headers <span class="text-gray-400 font-normal">(optional)</span></label>
                <div class="space-y-2">
                    <template x-for="(h, i) in headers" :key="i">
                        <div class="flex gap-2">
                            <input type="text" :name="`headers[${i}][key]`" x-model="h.key"
                                placeholder="X-Secret-Token"
                                class="flex-1 px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                            <input type="text" :name="`headers[${i}][value]`" x-model="h.value"
                                placeholder="my-secret"
                                class="flex-1 px-3 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="removeHeader(i)"
                                class="text-gray-300 hover:text-red-400 text-lg leading-none px-1">×</button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addHeader()"
                    class="mt-2 text-xs text-blue-600 hover:text-blue-700 font-medium">
                    + Add header
                </button>
            </div>
        </div>

        <div class="flex gap-2 mt-4">
            <a href="{{ route('admin.webhooks.index') }}"
                class="flex-1 py-2.5 text-sm font-medium text-center text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </a>
            <button type="submit"
                class="flex-1 py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                {{ $webhook ? 'Save Changes' : 'Create Webhook' }}
            </button>
        </div>
    </form>
</div>
@endsection
