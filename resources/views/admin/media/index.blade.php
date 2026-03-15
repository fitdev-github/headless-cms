@extends('layouts.admin')
@section('title', 'Media Library')

@section('content')
<div class="p-6" x-data="mediaLibrary({{ $picker ? 'true' : 'false' }})">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Media Library</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $media->total() }} files</p>
        </div>
        <label class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Upload files
            <input type="file" multiple class="hidden" @change="uploadFiles($event)">
        </label>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-2 mb-5">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name…"
            class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="type" onchange="this.form.submit()"
            class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All types</option>
            <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>Images</option>
            <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>Videos</option>
            <option value="doc" {{ request('type') === 'doc' ? 'selected' : '' }}>Documents</option>
        </select>
        @if($folders->isNotEmpty())
        <select name="folder" onchange="this.form.submit()"
            class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All folders</option>
            @foreach($folders as $folder)
            <option value="{{ $folder }}" {{ request('folder') === $folder ? 'selected' : '' }}>{{ $folder }}</option>
            @endforeach
        </select>
        @endif
        @if(request()->hasAny(['q','type','folder']))
            <a href="{{ route('admin.media.index') }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                Clear
            </a>
        @endif
    </form>

    {{-- Upload progress --}}
    <div x-show="uploading" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700 flex items-center gap-2">
        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Uploading <span x-text="uploadProgress"></span>…
    </div>

    {{-- Grid --}}
    @if($media->isEmpty())
        <div class="bg-white border border-dashed border-gray-200 rounded-xl py-16 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm text-gray-400">No media files yet. Upload something!</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($media as $file)
            <div class="group relative bg-white border border-gray-100 rounded-xl overflow-hidden cursor-pointer hover:border-blue-300 transition-colors"
                @click="{{ $picker ? 'selectMedia('.json_encode($file->toApiArray()).')' : 'openDetail('.json_encode($file->toApiArray()).')' }}">
                {{-- Thumbnail --}}
                <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                    @if($file->isImage())
                        <img src="{{ $file->url }}" alt="{{ $file->alt }}" class="w-full h-full object-cover">
                    @else
                        <div class="text-center p-2">
                            <svg class="w-8 h-8 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-xs text-gray-400 mt-1 truncate">{{ pathinfo($file->original_name, PATHINFO_EXTENSION) }}</p>
                        </div>
                    @endif
                </div>

                {{-- Name --}}
                <div class="p-2 border-t border-gray-50">
                    <p class="text-xs text-gray-600 truncate" title="{{ $file->original_name }}">{{ $file->original_name }}</p>
                    <p class="text-xs text-gray-400">{{ $file->formatted_size }}</p>
                </div>

                {{-- Hover delete (non-picker mode) --}}
                @unless($picker)
                <button @click.stop="deleteMedia({{ $file->id }})"
                    class="absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 text-white rounded-full text-xs items-center justify-center hidden group-hover:flex hover:bg-red-600 transition-colors">
                    ×
                </button>
                @endunless
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($media->hasPages())
        <div class="mt-5">
            {{ $media->appends(request()->query())->links() }}
        </div>
        @endif
    @endif

    {{-- Detail Modal --}}
    <div x-show="detail" x-cloak
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
        @click.self="detail = null">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 text-sm truncate" x-text="detail && detail.name"></h3>
                <button @click="detail = null" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <template x-if="detail">
                <div class="p-5">
                    <div class="aspect-video bg-gray-100 rounded-xl overflow-hidden mb-4 flex items-center justify-center">
                        <template x-if="detail.mime && detail.mime.startsWith('image/')">
                            <img :src="detail.url" class="max-w-full max-h-full object-contain">
                        </template>
                        <template x-if="!(detail.mime && detail.mime.startsWith('image/'))">
                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </template>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex gap-3"><span class="text-gray-400 w-20 flex-shrink-0">URL</span><a :href="detail.url" target="_blank" class="text-blue-600 hover:underline truncate flex-1" x-text="detail.url"></a></div>
                        <div class="flex gap-3"><span class="text-gray-400 w-20 flex-shrink-0">Size</span><span x-text="detail.size"></span></div>
                        <template x-if="detail.width">
                            <div class="flex gap-3"><span class="text-gray-400 w-20 flex-shrink-0">Dimensions</span><span x-text="detail.width + ' × ' + detail.height + ' px'"></span></div>
                        </template>
                    </div>
                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Alt text</label>
                        <input type="text" x-model="detail.alt" @change="updateAlt(detail)"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex gap-2 mt-4">
                        <a :href="detail.url" target="_blank"
                            class="flex-1 py-2 text-sm font-medium text-center border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Open file
                        </a>
                        <button @click="deleteMedia(detail.id)"
                            class="flex-1 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

@push('scripts')
<script>
function mediaLibrary(picker) {
    return {
        picker,
        uploading: false,
        uploadProgress: '',
        detail: null,

        async uploadFiles(e) {
            const files = Array.from(e.target.files);
            if (!files.length) return;
            this.uploading = true;
            const token = document.querySelector('meta[name="csrf-token"]').content;

            for (let i = 0; i < files.length; i++) {
                this.uploadProgress = `${i + 1}/${files.length}`;
                const fd = new FormData();
                fd.append('file', files[i]);
                await fetch('{{ route('admin.media.upload') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token },
                    body: fd
                });
            }
            this.uploading = false;
            window.location.reload();
        },

        openDetail(media) {
            this.detail = media;
        },

        selectMedia(media) {
            // Emit to parent window (for picker mode in iframe/popup)
            if (window.opener) {
                window.opener.postMessage({ type: 'media_selected', media }, '*');
                window.close();
            } else if (window.parent !== window) {
                window.parent.postMessage({ type: 'media_selected', media }, '*');
            }
        },

        async updateAlt(item) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            await fetch(`/admin/media-library/${item.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ alt: item.alt })
            });
        },

        async deleteMedia(id) {
            if (!confirm('Delete this file permanently?')) return;
            const token = document.querySelector('meta[name="csrf-token"]').content;
            await fetch(`/admin/media-library/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token }
            });
            this.detail = null;
            window.location.reload();
        }
    }
}
</script>
@endpush
@endsection
