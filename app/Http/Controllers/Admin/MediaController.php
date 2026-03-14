<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::orderByDesc('created_at');

        if ($request->filled('folder')) {
            $query->where('folder', $request->folder);
        }
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('original_name', 'like', '%'.$request->q.'%')
                  ->orWhere('alt', 'like', '%'.$request->q.'%');
            });
        }
        if ($request->filled('type')) {
            match ($request->type) {
                'image' => $query->where('mime_type', 'like', 'image/%'),
                'video' => $query->where('mime_type', 'like', 'video/%'),
                'doc'   => $query->whereNotIn('mime_type', [])->where(function($q) {
                    $q->where('mime_type', 'like', 'application/%')
                      ->orWhere('mime_type', 'like', 'text/%');
                }),
                default => null,
            };
        }

        $media   = $query->paginate(24)->withQueryString();
        $folders = Media::select('folder')->distinct()->whereNotNull('folder')->pluck('folder');
        $picker  = (bool) $request->boolean('picker');

        return view('admin.media.index', compact('media', 'folders', 'picker'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file'   => ['required', 'file', 'max:20480'], // 20MB
            'alt'    => ['nullable', 'string', 'max:255'],
            'folder' => ['nullable', 'string', 'max:100'],
        ]);

        $file   = $request->file('file');
        $folder = $request->input('folder', 'uploads');
        $folder = Str::slug($folder, '_') ?: 'uploads';

        $year  = now()->format('Y');
        $month = now()->format('m');
        $path  = Storage::disk('public')->putFile("{$folder}/{$year}/{$month}", $file);

        $width  = null;
        $height = null;
        if (str_starts_with($file->getMimeType(), 'image/') && function_exists('getimagesize')) {
            $info   = @getimagesize($file->getRealPath());
            $width  = $info[0] ?? null;
            $height = $info[1] ?? null;
        }

        $media = Media::create([
            'filename'      => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'width'         => $width,
            'height'        => $height,
            'path'          => $path,
            'alt'           => $request->input('alt', ''),
            'caption'       => '',
            'folder'        => $folder,
        ]);

        return response()->json($media->toApiArray());
    }

    public function update(Request $request, int $id)
    {
        $media = Media::findOrFail($id);
        $media->update([
            'alt'     => $request->input('alt', $media->alt),
            'caption' => $request->input('caption', $media->caption),
        ]);
        return response()->json($media->fresh()->toApiArray());
    }

    public function destroy(int $id)
    {
        $media = Media::findOrFail($id);
        Storage::disk('public')->delete($media->path);
        $media->forceDelete();
        return response()->json(['ok' => true]);
    }
}
