<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\Entry;
use App\Services\EntryService;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function __construct(protected EntryService $entryService) {}

    public function index(string $slug)
    {
        $ct = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);

        // Single type → redirect to edit the single entry (or create it)
        if ($ct->isSingle()) {
            $entry = Entry::where('content_type_id', $ct->id)->first();
            if ($entry) {
                return redirect()->route('admin.cm.edit', [$slug, $entry->id]);
            }
            return redirect()->route('admin.cm.create', $slug);
        }

        $query = Entry::with(['creator'])
            ->where('content_type_id', $ct->id)
            ->orderByDesc('updated_at');

        // Filter by status
        if (request('status')) {
            $query->where('status', request('status'));
        }

        // Search (naive - only over entry id + date; full search needs more)
        $entries = $query->paginate(20)->withQueryString();
        $fields  = $ct->fields()->orderBy('sort_order')->get();

        // Get title field (first text/richtext field)
        $titleField = $fields->firstWhere('type', 'text') ?? $fields->firstWhere('type', 'richtext') ?? $fields->first();

        // Load title values for listing
        $titleValues = [];
        if ($titleField) {
            $entryIds = $entries->pluck('id');
            $values   = \App\Models\EntryValue::whereIn('entry_id', $entryIds)
                ->where('field_id', $titleField->id)
                ->pluck('value_text', 'entry_id');
            $titleValues = $values->all();
        }

        return view('admin.entries.index', compact('ct', 'entries', 'fields', 'titleField', 'titleValues'));
    }

    public function create(string $slug)
    {
        $ct     = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);
        $fields = $ct->fields()->orderBy('sort_order')->get();
        return view('admin.entries.edit', compact('ct', 'fields'), ['entry' => null]);
    }

    public function store(Request $request, string $slug)
    {
        $ct = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);

        $status = $request->input('_status', 'draft');
        $entry  = Entry::create([
            'content_type_id' => $ct->id,
            'status'          => in_array($status, ['draft', 'published']) ? $status : 'draft',
            'created_by'      => auth()->id(),
            'published_at'    => $status === 'published' ? now() : null,
        ]);

        $fields = $ct->fields()->orderBy('sort_order')->get();
        $this->entryService->saveValues($entry, $request->except(['_token', '_method', '_status']), $fields);

        return redirect()->route('admin.cm.edit', [$slug, $entry->id])
            ->with('success', 'Entry created.');
    }

    public function edit(string $slug, int $id)
    {
        $ct    = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);
        $entry = Entry::where('content_type_id', $ct->id)->findOrFail($id);
        $fields = $ct->fields()->orderBy('sort_order')->get();

        $values = $this->entryService->getValues($entry);

        return view('admin.entries.edit', compact('ct', 'entry', 'fields', 'values'));
    }

    public function update(Request $request, string $slug, int $id)
    {
        $ct    = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);
        $entry = Entry::where('content_type_id', $ct->id)->findOrFail($id);

        $status = $request->input('_status', $entry->status);
        $entry->update([
            'status'       => in_array($status, ['draft', 'published']) ? $status : $entry->status,
            'published_at' => ($status === 'published' && !$entry->published_at) ? now() : $entry->published_at,
        ]);

        $fields = $ct->fields()->orderBy('sort_order')->get();
        $this->entryService->saveValues($entry, $request->except(['_token', '_method', '_status']), $fields);

        return back()->with('success', 'Entry saved.');
    }

    public function destroy(string $slug, int $id)
    {
        $ct    = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);
        $entry = Entry::where('content_type_id', $ct->id)->findOrFail($id);
        $entry->delete();

        if ($ct->isSingle()) {
            return redirect()->route('admin.cm.create', $slug)->with('success', 'Entry deleted.');
        }
        return redirect()->route('admin.cm.index', $slug)->with('success', 'Entry deleted.');
    }
}
