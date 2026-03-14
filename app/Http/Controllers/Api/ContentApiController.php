<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\Entry;
use App\Services\ApiQueryBuilder;
use App\Services\ApiResponseFormatter;
use App\Services\EntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentApiController extends Controller
{
    public function __construct(
        protected ApiQueryBuilder      $queryBuilder,
        protected ApiResponseFormatter $formatter,
        protected EntryService         $entryService,
    ) {}

    // GET /api/v1/{slug}
    public function index(Request $request, string $slug): JsonResponse
    {
        $ct = ContentType::findBySlug($slug);
        if (!$ct) {
            return $this->formatter->error('content-type.notFound', "Content type '{$slug}' not found.", 404);
        }

        // Single type → forward to find
        if ($ct->isSingle()) {
            return $this->find($request, $slug, null);
        }

        $query      = $this->queryBuilder->buildQuery($ct, $request);
        $paginated  = $this->queryBuilder->paginate($query, $request);
        $populate   = $this->queryBuilder->resolvePopulate($request);

        $entries    = $paginated['data'];
        $fields     = $ct->fields()->orderBy('sort_order')->get();

        $data = $entries->map(function (Entry $entry) use ($fields, $populate) {
            return $this->formatter->formatEntry($entry, $fields, $populate);
        });

        return response()->json([
            'data' => $data,
            'meta' => ['pagination' => $paginated['pagination']],
        ]);
    }

    // GET /api/v1/{slug}/{id}
    public function find(Request $request, string $slug, ?int $id): JsonResponse
    {
        $ct = ContentType::findBySlug($slug);
        if (!$ct) {
            return $this->formatter->error('content-type.notFound', "Content type '{$slug}' not found.", 404);
        }

        if ($ct->isSingle()) {
            $entry = Entry::where('content_type_id', $ct->id)
                ->where('status', 'published')
                ->first();
            if (!$entry) {
                return response()->json(['data' => null]);
            }
        } else {
            $entry = Entry::where('content_type_id', $ct->id)
                ->where('status', 'published')
                ->find($id);
            if (!$entry) {
                return $this->formatter->error('entry.notFound', "Entry not found.", 404);
            }
        }

        $fields   = $ct->fields()->orderBy('sort_order')->get();
        $populate = $this->queryBuilder->resolvePopulate($request);

        return response()->json([
            'data' => $this->formatter->formatEntry($entry, $fields, $populate),
        ]);
    }

    // POST /api/v1/{slug}
    public function create(Request $request, string $slug): JsonResponse
    {
        $ct = ContentType::findBySlug($slug);
        if (!$ct) {
            return $this->formatter->error('content-type.notFound', "Content type '{$slug}' not found.", 404);
        }

        $body = $request->input('data', $request->all());

        $status = isset($body['publishedAt']) ? 'published' : 'draft';

        $entry = Entry::create([
            'content_type_id' => $ct->id,
            'status'          => $status,
            'created_by'      => null,
            'published_at'    => $status === 'published' ? now() : null,
        ]);

        $fields = $ct->fields()->orderBy('sort_order')->get();
        $this->entryService->saveValues($entry, $body, $fields);

        $populate = $this->queryBuilder->resolvePopulate($request);

        return response()->json([
            'data' => $this->formatter->formatEntry($entry->fresh(), $fields, $populate),
        ], 200);
    }

    // PUT /api/v1/{slug}/{id}
    public function update(Request $request, string $slug, int $id): JsonResponse
    {
        $ct = ContentType::findBySlug($slug);
        if (!$ct) {
            return $this->formatter->error('content-type.notFound', "Content type '{$slug}' not found.", 404);
        }

        $entry = Entry::where('content_type_id', $ct->id)->find($id);
        if (!$entry) {
            return $this->formatter->error('entry.notFound', "Entry not found.", 404);
        }

        $body = $request->input('data', $request->all());

        if (isset($body['publishedAt'])) {
            $entry->update(['status' => 'published', 'published_at' => now()]);
        }

        $fields = $ct->fields()->orderBy('sort_order')->get();
        $this->entryService->saveValues($entry, $body, $fields);

        $populate = $this->queryBuilder->resolvePopulate($request);

        return response()->json([
            'data' => $this->formatter->formatEntry($entry->fresh(), $fields, $populate),
        ]);
    }

    // DELETE /api/v1/{slug}/{id}
    public function delete(Request $request, string $slug, int $id): JsonResponse
    {
        $ct = ContentType::findBySlug($slug);
        if (!$ct) {
            return $this->formatter->error('content-type.notFound', "Content type '{$slug}' not found.", 404);
        }

        $entry = Entry::where('content_type_id', $ct->id)->find($id);
        if (!$entry) {
            return $this->formatter->error('entry.notFound', "Entry not found.", 404);
        }

        $fields   = $ct->fields()->orderBy('sort_order')->get();
        $populate = $this->queryBuilder->resolvePopulate($request);
        $deleted  = $this->formatter->formatEntry($entry, $fields, $populate);

        $entry->delete();

        return response()->json(['data' => $deleted]);
    }
}
