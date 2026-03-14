@extends('layouts.admin')
@section('title', $contentType ? 'Edit: '.$contentType->display_name : 'Create Content Type')

@section('content')
<div class="p-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.ctb.index') }}" class="text-sm text-gray-400 hover:text-gray-600">Content-Type Builder</a>
        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-sm text-gray-900 font-medium">{{ $contentType ? $contentType->display_name : 'Create '.ucfirst($kind ?? 'collection').' Type' }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Type Info --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-100 rounded-xl p-5">
                <h2 class="text-sm font-semibold text-gray-900 mb-4">
                    {{ $contentType ? 'Type Settings' : 'New Content Type' }}
                </h2>

                @if($contentType)
                    <form method="POST" action="{{ route('admin.ctb.update', $contentType->id) }}">
                        @csrf @method('PUT')
                @else
                    <form method="POST" action="{{ route('admin.ctb.store') }}">
                        @csrf
                        <input type="hidden" name="type" value="{{ $kind ?? 'collection' }}">
                @endif

                @if(!$contentType)
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Display Name *</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $contentType->display_name ?? '') }}"
                        placeholder="e.g. Blog Articles"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Shown in the admin panel.</p>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">API ID (singular) *</label>
                    <input type="text" name="singular_name" value="{{ old('singular_name', $contentType->singular_name ?? '') }}"
                        placeholder="article"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    <p class="text-xs text-gray-400 mt-1">Lowercase, letters and hyphens only.</p>
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">API ID (plural) *</label>
                    <input type="text" name="plural_name" value="{{ old('plural_name', $contentType->plural_name ?? '') }}"
                        placeholder="articles"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    <p class="text-xs text-gray-400 mt-1">Used as the API endpoint slug.</p>
                </div>
                @else
                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Display Name *</label>
                    <input type="text" name="display_name" value="{{ old('display_name', $contentType->display_name) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-3">
                    <p class="text-xs font-medium text-gray-600 mb-1">API Endpoint</p>
                    <code class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">/api/v1/{{ $contentType->plural_name }}</code>
                </div>
                @endif

                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Icon (emoji)</label>
                    <input type="text" name="icon" value="{{ old('icon', $contentType->icon ?? '') }}"
                        placeholder="📝" maxlength="4"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $contentType->description ?? '') }}</textarea>
                </div>

                <div class="mb-4 flex items-center gap-2">
                    <input type="hidden" name="draft_publish" value="0">
                    <input type="checkbox" name="draft_publish" id="draft_publish" value="1"
                        {{ old('draft_publish', $contentType->draft_publish ?? true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="draft_publish" class="text-xs text-gray-600">Enable Draft/Publish system</label>
                </div>

                <button type="submit"
                    class="w-full py-2.5 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    {{ $contentType ? 'Save Changes' : 'Create & Add Fields →' }}
                </button>
                </form>

                @if($contentType)
                <form method="POST" action="{{ route('admin.ctb.destroy', $contentType->id) }}" class="mt-2"
                    onsubmit="return confirm('Delete this content type and ALL its entries? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-full py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors border border-red-200">
                        Delete Content Type
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Right: Field Builder --}}
        @if($contentType)
        <div class="lg:col-span-2"
            x-data="fieldBuilder({{ json_encode($fields) }}, {{ $contentType->id }})">

            <div class="bg-white border border-gray-100 rounded-xl">
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Fields</h2>
                    <button @click="showAdd = true"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add another field
                    </button>
                </div>

                {{-- Field list --}}
                <div class="divide-y divide-gray-50" id="fields-list">
                    <template x-if="fields.length === 0">
                        <div class="px-5 py-10 text-center">
                            <p class="text-sm text-gray-400 mb-2">No fields yet.</p>
                            <button @click="showAdd = true" class="text-sm text-blue-600 font-medium hover:underline">Add your first field →</button>
                        </div>
                    </template>
                    <template x-for="(field, i) in fields" :key="field.id">
                        <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 transition-colors">
                            <div class="cursor-grab text-gray-300 hover:text-gray-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900" x-text="field.display_name"></span>
                                    <code class="text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded" x-text="field.name"></code>
                                </div>
                                <span class="text-xs text-gray-400" x-text="field.type"></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="editField(field)"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="deleteField(field)"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Add/Edit Field Modal --}}
            <div x-show="showAdd || showEdit" x-cloak
                class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
                @click.self="closeModal()">

                <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900" x-text="showEdit ? 'Edit Field' : 'Add New Field'"></h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="p-5">
                        {{-- Field type picker (only shown when adding) --}}
                        <template x-if="showAdd && !selectedType">
                            <div>
                                <p class="text-xs font-medium text-gray-600 mb-3">Select a field type</p>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach([
                                        ['text',        'Text',        '✏️'],
                                        ['textarea',    'Long Text',   '📝'],
                                        ['richtext',    'Rich Text',   '📄'],
                                        ['number',      'Number',      '🔢'],
                                        ['boolean',     'Boolean',     '☑️'],
                                        ['date',        'Date',        '📅'],
                                        ['datetime',    'DateTime',    '🕐'],
                                        ['email',       'Email',       '📧'],
                                        ['password',    'Password',    '🔑'],
                                        ['enumeration', 'Enumeration', '📋'],
                                        ['uid',         'UID',         '🔗'],
                                        ['media',       'Media',       '🖼️'],
                                        ['json',        'JSON',        '{}'],
                                        ['relation',    'Relation',    '↔️'],
                                    ] as [$type, $label, $icon])
                                    <button @click="selectedType = '{{ $type }}'"
                                        class="flex flex-col items-center gap-1.5 p-3 border border-gray-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition-colors text-sm">
                                        <span class="text-xl">{{ $icon }}</span>
                                        <span class="text-xs text-gray-700 font-medium">{{ $label }}</span>
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </template>

                        {{-- Field form --}}
                        <template x-if="selectedType || showEdit">
                            <div>
                                <div class="mb-3" x-show="showAdd">
                                    <div class="flex items-center gap-2 p-2 bg-blue-50 rounded-lg">
                                        <span class="text-sm font-medium text-blue-700" x-text="'Type: ' + selectedType"></span>
                                        <button @click="selectedType = null" class="ml-auto text-xs text-blue-600 hover:underline">Change</button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Display Name *</label>
                                    <input type="text" x-model="form.display_name" placeholder="e.g. Article Title"
                                        @input="if(showAdd) form.name = form.display_name.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '')"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="mb-3" x-show="showAdd">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Field Name (camelCase) *</label>
                                    <input type="text" x-model="form.name" placeholder="e.g. articleTitle"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                                </div>

                                {{-- Options by type --}}
                                <div class="space-y-2 mb-4">
                                    <template x-if="selectedType === 'text' || selectedType === 'textarea' || selectedType === 'email'">
                                        <div class="space-y-2">
                                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                                <input type="checkbox" x-model="form.options.required" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Required
                                            </label>
                                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                                <input type="checkbox" x-model="form.options.unique" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Unique
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Min length</label>
                                                    <input type="number" x-model.number="form.options.minLength" min="0" placeholder="0"
                                                        class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Max length</label>
                                                    <input type="number" x-model.number="form.options.maxLength" min="0" placeholder="255"
                                                        class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="selectedType === 'number'">
                                        <div class="space-y-2">
                                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                                <input type="checkbox" x-model="form.options.required" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Required
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Min</label>
                                                    <input type="number" x-model.number="form.options.min" placeholder="—"
                                                        class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Max</label>
                                                    <input type="number" x-model.number="form.options.max" placeholder="—"
                                                        class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="selectedType === 'enumeration'">
                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-600 mb-2">
                                                <input type="checkbox" x-model="form.options.required" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Required
                                            </label>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Values (one per line) *</label>
                                            <textarea x-model="enumValuesText" @change="form.options.enum_values = enumValuesText.split('\n').map(s=>s.trim()).filter(Boolean)"
                                                rows="4" placeholder="draft&#10;published&#10;archived"
                                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono resize-none"></textarea>
                                        </div>
                                    </template>

                                    <template x-if="selectedType === 'uid'">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Auto-generate from field</label>
                                            <input type="text" x-model="form.options.target_field" placeholder="e.g. title"
                                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                                        </div>
                                    </template>

                                    <template x-if="selectedType === 'relation'">
                                        <div class="space-y-2">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Related Content Type *</label>
                                                <select x-model="form.options.relation_type_id"
                                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="">— Select —</option>
                                                    @foreach(\App\Models\ContentType::orderBy('display_name')->get() as $relCt)
                                                    <option value="{{ $relCt->id }}">{{ $relCt->display_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Relation Type</label>
                                                <select x-model="form.options.relation"
                                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    <option value="oneToOne">One to One</option>
                                                    <option value="oneToMany">One to Many</option>
                                                    <option value="manyToMany">Many to Many</option>
                                                </select>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="selectedType === 'media'">
                                        <div>
                                            <label class="flex items-center gap-2 text-xs text-gray-600">
                                                <input type="checkbox" x-model="form.options.multiple" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"> Allow multiple files
                                            </label>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="closeModal()" class="flex-1 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        Cancel
                                    </button>
                                    <button @click="saveField()"
                                        :disabled="saving"
                                        class="flex-1 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50">
                                        <span x-show="!saving" x-text="showEdit ? 'Save Changes' : 'Add Field'"></span>
                                        <span x-show="saving">Saving…</span>
                                    </button>
                                </div>

                                <div x-show="errorMsg" class="mt-2 p-2 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700" x-text="errorMsg"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="lg:col-span-2">
            <div class="bg-white border border-dashed border-gray-200 rounded-xl p-10 text-center">
                <p class="text-sm text-gray-400">Create the content type first, then you can add fields.</p>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function fieldBuilder(initialFields, contentTypeId) {
    return {
        fields: initialFields,
        showAdd: false,
        showEdit: false,
        selectedType: null,
        saving: false,
        errorMsg: '',
        editingField: null,
        enumValuesText: '',
        form: { name: '', display_name: '', options: {} },

        editField(field) {
            this.editingField = field;
            this.selectedType = field.type;
            this.form = {
                name: field.name,
                display_name: field.display_name,
                options: Object.assign({}, field.options || {})
            };
            this.enumValuesText = (this.form.options.enum_values || []).join('\n');
            this.showEdit = true;
            this.errorMsg = '';
        },

        closeModal() {
            this.showAdd = false;
            this.showEdit = false;
            this.selectedType = null;
            this.form = { name: '', display_name: '', options: {} };
            this.enumValuesText = '';
            this.editingField = null;
            this.errorMsg = '';
        },

        async saveField() {
            this.saving = true;
            this.errorMsg = '';
            const token = document.querySelector('meta[name="csrf-token"]').content;

            try {
                let url, method;
                if (this.showEdit && this.editingField) {
                    url = `/admin/content-type-builder/${contentTypeId}/fields/${this.editingField.id}`;
                    method = 'PUT';
                } else {
                    url = `/admin/content-type-builder/${contentTypeId}/fields`;
                    method = 'POST';
                }

                const payload = {
                    name: this.form.name,
                    display_name: this.form.display_name,
                    type: this.selectedType,
                    options: this.form.options,
                };

                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (!res.ok) {
                    this.errorMsg = data.error || data.message || 'Failed to save.';
                } else {
                    if (this.showEdit) {
                        const i = this.fields.findIndex(f => f.id === this.editingField.id);
                        if (i >= 0) this.fields[i] = data.field;
                    } else {
                        this.fields.push(data.field);
                    }
                    this.closeModal();
                }
            } catch(e) {
                this.errorMsg = 'Network error.';
            }
            this.saving = false;
        },

        async deleteField(field) {
            if (!confirm(`Delete field "${field.display_name}"? This will also delete all stored values for this field.`)) return;
            const token = document.querySelector('meta[name="csrf-token"]').content;
            await fetch(`/admin/content-type-builder/${contentTypeId}/fields/${field.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token }
            });
            this.fields = this.fields.filter(f => f.id !== field.id);
        }
    }
}
</script>
@endpush
@endsection
