@extends('layouts.admin')
@section('title', ($entry ? 'Edit' : 'Create').' — '.$ct->display_name)

@section('content')
<div class="p-6">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-3 mb-6">
        @if($ct->isCollection())
            <a href="{{ route('admin.cm.index', $ct->plural_name) }}" class="text-sm text-gray-400 hover:text-gray-600">{{ $ct->display_name }}</a>
        @else
            <span class="text-sm text-gray-400">{{ $ct->display_name }}</span>
        @endif
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm text-gray-900 font-medium">{{ $entry ? 'Edit Entry #'.$entry->id : 'Create Entry' }}</span>
    </div>

    @if($fields->isEmpty())
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-5 text-sm text-yellow-800">
            <strong>No fields defined.</strong>
            <a href="{{ route('admin.ctb.edit', $ct->id) }}" class="underline ml-1">Go to Content-Type Builder →</a>
        </div>
    @else

    @php
        $formAction = $entry
            ? route('admin.cm.update', [$ct->plural_name, $entry->id])
            : route('admin.cm.store', $ct->plural_name);
        $values = $values ?? [];
    @endphp

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data" id="entry-form">
        @csrf
        @if($entry) @method('PUT') @endif

        <div class="flex gap-6">
            {{-- Main fields --}}
            <div class="flex-1 min-w-0 space-y-4">
                @foreach($fields as $field)
                    @unless(in_array($field->type, ['password']))
                    <div class="bg-white border border-gray-100 rounded-xl p-5 [&_.mb-6]:mb-0">
                        {!! app(\App\Services\FieldRenderer::class)->renderInput($field, $values[$field->name] ?? null) !!}
                    </div>
                    @endunless
                @endforeach
            </div>

            {{-- Sidebar --}}
            <div class="w-64 flex-shrink-0 space-y-4">
                {{-- Publish panel --}}
                <div class="bg-white border border-gray-100 rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">
                        {{ $ct->draft_publish ? 'Publication' : 'Save' }}
                    </h3>

                    @if($entry)
                        <div class="text-xs text-gray-400 space-y-1 mb-4">
                            <div class="flex justify-between">
                                <span>Created</span>
                                <span>{{ $entry->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Updated</span>
                                <span>{{ $entry->updated_at->diffForHumans() }}</span>
                            </div>
                            @if($ct->draft_publish)
                            <div class="flex justify-between">
                                <span>Status</span>
                                <span class="font-medium {{ $entry->status === 'published' ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ ucfirst($entry->status) }}
                                </span>
                            </div>
                            @endif
                        </div>
                    @endif

                    @if($ct->draft_publish)
                        <div class="space-y-2">
                            <button type="submit" name="_status" value="draft"
                                class="w-full py-2 text-sm font-medium border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                Save as Draft
                            </button>
                            <button type="submit" name="_status" value="published"
                                class="w-full py-2 text-sm font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                {{ ($entry && $entry->status === 'published') ? 'Update' : 'Publish' }}
                            </button>
                        </div>
                    @else
                        <button type="submit"
                            class="w-full py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            {{ $entry ? 'Save Changes' : 'Create Entry' }}
                        </button>
                    @endif

                    @if($entry)
                    <form method="POST" action="{{ route('admin.cm.destroy', [$ct->plural_name, $entry->id]) }}"
                        onsubmit="return confirm('Delete this entry permanently?')" class="mt-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                            Delete entry
                        </button>
                    </form>
                    @endif
                </div>

                {{-- Password fields --}}
                @foreach($fields->where('type', 'password') as $field)
                <div class="bg-white border border-gray-100 rounded-xl p-4 [&_.mb-6]:mb-0">
                    {!! app(\App\Services\FieldRenderer::class)->renderInput($field, null) !!}
                </div>
                @endforeach

                {{-- API info --}}
                @if($entry)
                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">API</p>
                    <code class="text-xs text-gray-600 break-all">/api/v1/{{ $ct->plural_name }}/{{ $entry->id }}</code>
                </div>
                @endif
            </div>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Auto-generate UID fields
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-uid-source]').forEach(function(uidInput) {
        const sourceName = uidInput.dataset.uidSource;
        const sourceEl = document.querySelector('[name="' + sourceName + '"]');
        if (!sourceEl) return;
        sourceEl.addEventListener('input', function () {
            if (!uidInput.dataset.modified) {
                uidInput.value = sourceEl.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
            }
        });
        uidInput.addEventListener('input', function () {
            uidInput.dataset.modified = '1';
        });
    });
});
</script>
@endpush
