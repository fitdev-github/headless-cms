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
        <span class="text-sm text-gray-900 font-medium">
            @if($entry)
                Edit Entry #{{ $entry->id }}
                @if($ct->localized && $entry->locale)
                    <span class="ml-2 px-2 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700 rounded uppercase">{{ $entry->locale }}</span>
                @endif
            @else
                Create Entry
                @if($ct->localized && isset($targetLocale))
                    <span class="ml-2 px-2 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700 rounded uppercase">{{ $targetLocale }}</span>
                @endif
            @endif
        </span>
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

        {{-- Hidden locale fields --}}
        @if($ct->localized)
        <input type="hidden" name="_locale" value="{{ $entry ? ($entry->locale ?? ($defaultLocale ?? 'en')) : ($targetLocale ?? ($defaultLocale ?? 'en')) }}">
        <input type="hidden" name="_locale_group_id" value="{{ $entry ? ($entry->locale_group_id ?? '') : ($localeGroupId ?? '') }}">
        @endif

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
                    <button type="submit" form="delete-entry-form"
                        onclick="return confirm('Delete this entry permanently?')"
                        class="w-full mt-2 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                        Delete entry
                    </button>
                    @endif
                </div>

                {{-- Locale panel (i18n) --}}
                @if($ct->localized && $entry)
                @php
                    $entryLocale    = $entry->locale ?? ($defaultLocale ?? 'en');
                    $allLocales     = $locales ?? [];
                    $missingLocales = array_values(array_filter($allLocales, fn($l) => !isset($siblings[$l])));
                    $linkedLocales  = array_values(array_filter($allLocales, fn($l) => isset($siblings[$l]) && $siblings[$l]->id !== $entry->id));
                @endphp
                <div class="bg-white border border-gray-100 rounded-xl p-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">🌐 Locale</p>

                    {{-- Current locale badge --}}
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2.5 py-0.5 text-xs font-bold bg-indigo-100 text-indigo-700 rounded uppercase">
                            {{ $entryLocale }}
                        </span>
                        <span class="text-xs text-gray-400">current</span>
                    </div>

                    {{-- Translate to missing locales --}}
                    @if(!empty($missingLocales))
                    <div class="mb-3">
                        <p class="text-xs text-gray-500 mb-1.5">Translate to:</p>
                        <div class="space-y-1">
                            @foreach($missingLocales as $loc)
                            <form method="POST" action="{{ route('admin.cm.translate', [$ct->plural_name, $entry->id, $loc]) }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2 text-left text-xs px-2 py-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors font-medium">
                                    <span class="px-1.5 py-0.5 bg-blue-100 rounded text-blue-700 uppercase">{{ $loc }}</span>
                                    <span>+ Add translation</span>
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Links to existing translations --}}
                    @if(!empty($linkedLocales))
                    <div class="{{ !empty($missingLocales) ? 'border-t border-gray-100 pt-3' : '' }}">
                        <p class="text-xs text-gray-500 mb-1.5">Other translations:</p>
                        <div class="space-y-1">
                            @foreach($linkedLocales as $loc)
                            <a href="{{ route('admin.cm.edit', [$ct->plural_name, $siblings[$loc]->id]) }}"
                                class="flex items-center justify-between text-xs px-2 py-1.5 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                                <span class="px-1.5 py-0.5 bg-gray-100 rounded text-gray-700 font-bold uppercase">{{ $loc }}</span>
                                <span class="text-gray-400">Edit →</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

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

    {{-- Delete form lives OUTSIDE the main form to avoid nested-form HTML bug --}}
    @if($entry)
    <form id="delete-entry-form" method="POST"
        action="{{ route('admin.cm.destroy', [$ct->plural_name, $entry->id]) }}">
        @csrf @method('DELETE')
    </form>
    @endif

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
