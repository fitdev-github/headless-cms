<?php

namespace App\Services;

use App\Models\ContentType;
use App\Models\Entry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApiQueryBuilder
{
    // No constructor dependency — reusable across content types

    public function buildQuery(ContentType $ct, Request $request): Builder
    {
        $query = Entry::where('content_type_id', $ct->id)
                      ->with(['values.field']);

        // Load fields for filtering/sorting
        $ct->loadMissing('fields');

        // publicationState: 'live' (default) = published only, 'preview' = all
        $state = $request->input('publicationState', 'live');
        if ($state === 'live') {
            $query->where('status', 'published');
        }

        // Filters
        $filters = $request->input('filters', []);
        if (!empty($filters)) {
            $this->applyFilters($query, $filters, $ct);
        }

        // Sort
        $sort = $request->input('sort');
        if ($sort) {
            $this->applySort($query, $sort);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function paginate(Builder $query, Request $request): array
    {
        $pagination = $request->input('pagination', []);
        $page     = max(1, (int) ($pagination['page']     ?? 1));
        $pageSize = min(100, max(1, (int) ($pagination['pageSize'] ?? 25)));

        $total     = (clone $query)->count();
        $pageCount = $total > 0 ? (int) ceil($total / $pageSize) : 0;
        $items     = $query->offset(($page - 1) * $pageSize)->limit($pageSize)->get();

        return [
            'data'       => $items,
            'pagination' => [
                'page'      => $page,
                'pageSize'  => $pageSize,
                'pageCount' => $pageCount,
                'total'     => $total,
            ],
        ];
    }

    public function resolvePopulate(Request $request): array
    {
        $populate = $request->input('populate');
        if (!$populate) return [];
        if ($populate === '*') return ['*'];
        if (is_string($populate)) {
            return array_map('trim', explode(',', $populate));
        }
        if (is_array($populate)) {
            return array_values($populate);
        }
        return [];
    }

    // ─── Private helpers ───────────────────────────────────────────────────────

    private function applyFilters(Builder $query, array $filters, ContentType $ct): void
    {
        foreach ($filters as $key => $value) {
            if ($key === '$or') {
                $query->where(function (Builder $q) use ($value, $ct) {
                    foreach ($value as $condition) {
                        $q->orWhere(function (Builder $inner) use ($condition, $ct) {
                            $this->applyFilters($inner, $condition, $ct);
                        });
                    }
                });
            } elseif ($key === '$and') {
                foreach ($value as $condition) {
                    $this->applyFilters($query, $condition, $ct);
                }
            } else {
                $this->applyFieldFilter($query, $key, $value, $ct);
            }
        }
    }

    private function applyFieldFilter(Builder $query, string $fieldName, $operators, ContentType $ct): void
    {
        $builtIn = [
            'createdAt'   => 'created_at',
            'updatedAt'   => 'updated_at',
            'publishedAt' => 'published_at',
            'status'      => 'status',
            'id'          => 'id',
        ];

        if (isset($builtIn[$fieldName])) {
            $col = $builtIn[$fieldName];
            foreach ((array) $operators as $op => $val) {
                $this->applyOperator($query, $col, $op, $val);
            }
            return;
        }

        $field = $ct->fields->firstWhere('name', $fieldName);
        if (!$field) return;

        $valueCol = $field->getValueColumn();
        $query->whereHas('values', function (Builder $q) use ($field, $valueCol, $operators) {
            $q->where('field_id', $field->id);
            foreach ((array) $operators as $op => $val) {
                $this->applyOperator($q, $valueCol, $op, $val);
            }
        });
    }

    private function applyOperator(Builder $q, string $col, string $op, $val): void
    {
        match ($op) {
            '$eq'         => $q->where($col, '=', $val),
            '$ne'         => $q->where($col, '!=', $val),
            '$lt'         => $q->where($col, '<', $val),
            '$lte'        => $q->where($col, '<=', $val),
            '$gt'         => $q->where($col, '>', $val),
            '$gte'        => $q->where($col, '>=', $val),
            '$contains'   => $q->where($col, 'like', "%{$val}%"),
            '$containsi'  => $q->where($col, 'like', "%{$val}%"),
            '$startsWith' => $q->where($col, 'like', "{$val}%"),
            '$endsWith'   => $q->where($col, 'like', "%{$val}"),
            '$null'       => $val ? $q->whereNull($col) : $q->whereNotNull($col),
            '$notNull'    => $val ? $q->whereNotNull($col) : $q->whereNull($col),
            '$in'         => $q->whereIn($col, (array) $val),
            '$notIn'      => $q->whereNotIn($col, (array) $val),
            '$between'    => is_array($val) && count($val) === 2
                                ? $q->whereBetween($col, [$val[0], $val[1]])
                                : null,
            default       => null,
        };
    }

    private function applySort(Builder $query, $sort): void
    {
        $sorts   = is_array($sort) ? $sort : [$sort];
        $builtIn = [
            'createdAt'   => 'created_at',
            'updatedAt'   => 'updated_at',
            'publishedAt' => 'published_at',
            'id'          => 'id',
            'status'      => 'status',
        ];

        foreach ($sorts as $s) {
            $parts = explode(':', $s);
            $col   = $parts[0];
            $dir   = strtolower($parts[1] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            if (isset($builtIn[$col])) {
                $query->orderBy($builtIn[$col], $dir);
            }
        }
    }
}
