<?php

namespace App\Services;

use App\Models\ContentType;
use App\Models\Entry;
use App\Models\Field;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ApiResponseFormatter
{
    public function __construct(private EntryService $entryService) {}

    /**
     * Format a single entry for API output.
     * $fieldsOrType — either a ContentType model or a pre-loaded Collection of Field models.
     */
    public function formatEntry(Entry $entry, ContentType|Collection $fieldsOrType, array $populate = []): array
    {
        $fields = $fieldsOrType instanceof ContentType
            ? $fieldsOrType->fields
            : $fieldsOrType;

        $values     = $this->entryService->getValues($entry);
        $attributes = [
            'createdAt'   => $entry->created_at?->toISOString(),
            'updatedAt'   => $entry->updated_at?->toISOString(),
            'publishedAt' => $entry->published_at?->toISOString(),
        ];

        foreach ($fields as $field) {
            /** @var Field $field */
            if ($field->isPrivate() || $field->type === 'password') continue;

            $value = $values[$field->name] ?? null;
            $doPopulate = in_array('*', $populate) || in_array($field->name, $populate);

            if ($field->type === 'media') {
                $attributes[$field->name] = $doPopulate
                    ? $this->formatMediaValue($value)
                    : ['data' => null];
            } elseif ($field->type === 'relation') {
                $attributes[$field->name] = $doPopulate
                    ? $this->formatRelationValue($value, $field)
                    : ['data' => null];
            } else {
                $attributes[$field->name] = $value;
            }
        }

        return ['id' => $entry->id, 'attributes' => $attributes];
    }

    /** Return a Strapi-style error JSON response. */
    public function error(string $name, string $message, int $status = 400, array $details = []): JsonResponse
    {
        return response()->json([
            'data'  => null,
            'error' => [
                'status'  => $status,
                'name'    => $name,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    private function formatMediaValue($value): array
    {
        if (!$value) return ['data' => null];

        // Single: {id: 1, ...}
        if (is_array($value) && isset($value['id'])) {
            $media = Media::find($value['id']);
            return $media
                ? ['data' => ['id' => $media->id, 'attributes' => $this->mediaAttrs($media)]]
                : ['data' => null];
        }

        // Multiple: [{id, ...}, ...]
        if (is_array($value) && !empty($value) && isset($value[0]['id'])) {
            $ids   = array_column($value, 'id');
            $items = Media::whereIn('id', $ids)->get();
            return [
                'data' => $items->map(fn($m) => [
                    'id'         => $m->id,
                    'attributes' => $this->mediaAttrs($m),
                ])->values()->toArray(),
            ];
        }

        return ['data' => null];
    }

    private function mediaAttrs(Media $m): array
    {
        return [
            'name'            => $m->original_name,
            'alternativeText' => $m->alt,
            'caption'         => $m->caption,
            'url'             => $m->url,
            'mime'            => $m->mime_type,
            'size'            => round($m->size / 1024, 2), // KB
            'width'           => $m->width,
            'height'          => $m->height,
            'createdAt'       => $m->created_at?->toISOString(),
            'updatedAt'       => $m->updated_at?->toISOString(),
        ];
    }

    private function formatRelationValue($value, Field $field): array
    {
        if (!$value) return ['data' => null];

        $relType = $field->getRelationContentType();
        if (!$relType) return ['data' => null];

        $ids    = is_array($value) ? $value : [$value];
        $ids    = array_filter(array_map('intval', $ids));
        $isMany = in_array($field->getOption('relation'), ['oneToMany', 'manyToMany']);

        $entries = Entry::whereIn('id', $ids)->get();

        if ($isMany) {
            return [
                'data' => $entries->map(fn($e) => [
                    'id'         => $e->id,
                    'attributes' => $this->entryService->getValues($e),
                ])->values()->toArray(),
            ];
        }

        $related = $entries->first();
        return $related
            ? ['data' => ['id' => $related->id, 'attributes' => $this->entryService->getValues($related)]]
            : ['data' => null];
    }
}
