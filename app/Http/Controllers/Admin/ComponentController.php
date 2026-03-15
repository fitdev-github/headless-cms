<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\ComponentField;
use Illuminate\Http\Request;

class ComponentController extends Controller
{
    // ─── Component CRUD ──────────────────────────────────────────────────────

    public function index()
    {
        $components = Component::withCount('fields')
            ->orderBy('category')
            ->orderBy('display_name')
            ->get();

        return view('admin.components.index', compact('components'));
    }

    public function create()
    {
        return view('admin.components.builder', ['component' => null, 'fields' => collect()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:150',
            'name'         => 'required|string|max:100|regex:/^[a-z0-9]+\.[a-z0-9-]+$/|unique:components,name',
            'icon'         => 'nullable|string|max:10',
            'category'     => 'nullable|string|max:50',
        ]);

        $component = Component::create($validated);

        return redirect()->route('admin.components.edit', $component->id)
            ->with('success', 'Component created. Now add fields.');
    }

    public function edit(int $id)
    {
        $component = Component::findOrFail($id);
        $fields    = $component->fields()->orderBy('sort_order')->get();

        return view('admin.components.builder', compact('component', 'fields'));
    }

    public function update(Request $request, int $id)
    {
        $component = Component::findOrFail($id);

        $validated = $request->validate([
            'display_name' => 'required|string|max:150',
            'icon'         => 'nullable|string|max:10',
            'category'     => 'nullable|string|max:50',
        ]);

        $component->update($validated);

        return back()->with('success', 'Component updated.');
    }

    public function destroy(int $id)
    {
        $component = Component::findOrFail($id);
        $component->delete();

        return redirect()->route('admin.components.index')
            ->with('success', 'Component deleted.');
    }

    // ─── Field management (AJAX/JSON) ────────────────────────────────────────

    public function addField(Request $request, int $id)
    {
        $component = Component::findOrFail($id);

        $validated = $request->validate([
            'name'         => 'required|string|max:100|alpha_dash',
            'display_name' => 'required|string|max:150',
            'type'         => 'required|string|max:50',
            'options'      => 'nullable|array',
        ]);

        // Prevent nesting components inside components
        if (in_array($validated['type'], ['component', 'dynamiczone'])) {
            return response()->json(['error' => 'Components cannot be nested inside other components.'], 422);
        }

        // Unique name within this component
        $exists = ComponentField::where('component_id', $id)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json(['error' => "Field name '{$validated['name']}' already exists in this component."], 422);
        }

        $sortOrder = ComponentField::where('component_id', $id)->max('sort_order') + 1;

        $field = ComponentField::create(array_merge($validated, [
            'component_id' => $id,
            'sort_order'   => $sortOrder,
        ]));

        return response()->json(['field' => $field]);
    }

    public function updateField(Request $request, int $id, int $fid)
    {
        $field = ComponentField::where('component_id', $id)->findOrFail($fid);

        $validated = $request->validate([
            'display_name' => 'required|string|max:150',
            'options'      => 'nullable|array',
        ]);

        $field->update($validated);

        return response()->json(['field' => $field->fresh()]);
    }

    public function deleteField(int $id, int $fid)
    {
        $field = ComponentField::where('component_id', $id)->findOrFail($fid);
        $field->delete();

        return response()->json(['ok' => true]);
    }
}
