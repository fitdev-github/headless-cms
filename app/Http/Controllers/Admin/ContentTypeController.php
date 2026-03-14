<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentTypeController extends Controller
{
    public function index()
    {
        $collections = ContentType::where('type', 'collection')->orderBy('sort_order')->get();
        $singles     = ContentType::where('type', 'single')->orderBy('sort_order')->get();
        return view('admin.content-types.index', compact('collections', 'singles'));
    }

    public function create(string $kind)
    {
        abort_if(!in_array($kind, ['collection', 'single']), 404);
        return view('admin.content-types.builder', ['contentType' => null, 'kind' => $kind, 'fields' => collect()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'display_name'  => ['required', 'string', 'max:255'],
            'singular_name' => ['required', 'string', 'regex:/^[a-z][a-z0-9-]*$/'],
            'plural_name'   => ['required', 'string', 'regex:/^[a-z][a-z0-9-]*$/'],
            'type'          => ['required', 'in:collection,single'],
            'description'   => ['nullable', 'string'],
            'icon'          => ['nullable', 'string', 'max:10'],
            'draft_publish' => ['boolean'],
        ]);

        $ct = ContentType::create([
            'display_name'  => $data['display_name'],
            'singular_name' => $data['singular_name'],
            'plural_name'   => $data['plural_name'],
            'type'          => $data['type'],
            'description'   => $data['description'] ?? null,
            'icon'          => $data['icon'] ?? null,
            'draft_publish' => $request->boolean('draft_publish', true),
            'sort_order'    => ContentType::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.ctb.edit', $ct->id)
            ->with('success', 'Content type created. Now add fields.');
    }

    public function edit(int $id)
    {
        $contentType = ContentType::withTrashed()->findOrFail($id);
        $fields      = $contentType->fields()->orderBy('sort_order')->get();
        return view('admin.content-types.builder', compact('contentType', 'fields'));
    }

    public function update(Request $request, int $id)
    {
        $ct = ContentType::findOrFail($id);

        $data = $request->validate([
            'display_name'  => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'icon'          => ['nullable', 'string', 'max:10'],
            'draft_publish' => ['boolean'],
        ]);

        $ct->update([
            'display_name'  => $data['display_name'],
            'description'   => $data['description'] ?? null,
            'icon'          => $data['icon'] ?? null,
            'draft_publish' => $request->boolean('draft_publish', true),
        ]);

        return back()->with('success', 'Content type updated.');
    }

    public function destroy(int $id)
    {
        $ct = ContentType::findOrFail($id);
        $ct->delete();
        return redirect()->route('admin.ctb.index')->with('success', 'Content type deleted.');
    }

    // ─── Field AJAX ────────────────────────────────────────────────────────────

    public function addField(Request $request, int $id)
    {
        $ct = ContentType::findOrFail($id);

        $data = $request->validate([
            'name'         => ['required', 'string', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/'],
            'display_name' => ['required', 'string', 'max:255'],
            'type'         => ['required', 'string'],
            'options'      => ['nullable', 'array'],
        ]);

        // Ensure unique name within content type
        if ($ct->fields()->where('name', $data['name'])->exists()) {
            return response()->json(['error' => 'A field with that name already exists.'], 422);
        }

        $field = $ct->fields()->create([
            'name'         => $data['name'],
            'display_name' => $data['display_name'],
            'type'         => $data['type'],
            'options'      => $data['options'] ?? null,
            'sort_order'   => $ct->fields()->max('sort_order') + 1,
        ]);

        return response()->json(['field' => $field]);
    }

    public function updateField(Request $request, int $id, int $fid)
    {
        $field = Field::where('content_type_id', $id)->findOrFail($fid);

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'options'      => ['nullable', 'array'],
        ]);

        $field->update([
            'display_name' => $data['display_name'],
            'options'      => $data['options'] ?? $field->options,
        ]);

        return response()->json(['field' => $field->fresh()]);
    }

    public function deleteField(int $id, int $fid)
    {
        $field = Field::where('content_type_id', $id)->findOrFail($fid);
        $field->delete();
        return response()->json(['ok' => true]);
    }

    public function reorderFields(Request $request, int $id)
    {
        $request->validate(['order' => ['required', 'array']]);

        foreach ($request->order as $i => $fieldId) {
            Field::where('content_type_id', $id)->where('id', $fieldId)
                ->update(['sort_order' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
