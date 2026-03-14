<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\ApiResponseFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadApiController extends Controller
{
    public function __construct(protected ApiResponseFormatter $formatter) {}

    // POST /api/v1/upload
    public function upload(Request $request): JsonResponse
    {
        if (!$request->hasFile('files')) {
            return $this->formatter->error('upload.noFiles', 'No files were uploaded.', 400);
        }

        $files    = is_array($request->file('files')) ? $request->file('files') : [$request->file('files')];
        $results  = [];
        $folder   = Str::slug($request->input('path', 'uploads'), '_') ?: 'uploads';
        $year     = now()->format('Y');
        $month    = now()->format('m');

        foreach ($files as $file) {
            if (!$file->isValid()) continue;

            $path = Storage::disk('public')->putFile("{$folder}/{$year}/{$month}", $file);

            $width = $height = null;
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
                'alt'           => '',
                'caption'       => '',
                'folder'        => $folder,
            ]);

            $results[] = $media->toApiArray();
        }

        // Strapi returns array of file objects at root level
        return response()->json($results);
    }

    // GET /api/v1/upload/files
    public function files(Request $request): JsonResponse
    {
        $query = Media::orderByDesc('created_at');

        if ($request->filled('filters[folder][$eq]')) {
            $query->where('folder', $request->input('filters[folder][$eq]'));
        }

        $page     = max(1, (int) $request->input('pagination[page]', 1));
        $pageSize = min(100, max(1, (int) $request->input('pagination[pageSize]', 25)));

        $paginated = $query->paginate($pageSize, ['*'], 'page', $page);

        $data = $paginated->map(fn(Media $m) => $m->toApiArray())->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'pagination' => [
                    'page'      => $paginated->currentPage(),
                    'pageSize'  => $paginated->perPage(),
                    'pageCount' => $paginated->lastPage(),
                    'total'     => $paginated->total(),
                ],
            ],
        ]);
    }

    // GET /api/v1/upload/files/{id}
    public function fileById(int $id): JsonResponse
    {
        $media = Media::find($id);
        if (!$media) {
            return $this->formatter->error('upload.fileNotFound', 'File not found.', 404);
        }
        return response()->json($media->toApiArray());
    }

    // DELETE /api/v1/upload/files/{id}
    public function destroy(int $id): JsonResponse
    {
        $media = Media::find($id);
        if (!$media) {
            return $this->formatter->error('upload.fileNotFound', 'File not found.', 404);
        }
        Storage::disk('public')->delete($media->path);
        $result = $media->toApiArray();
        $media->forceDelete();
        return response()->json($result);
    }
}
