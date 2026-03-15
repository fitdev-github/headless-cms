<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\Entry;
use App\Models\Setting;
use App\Services\EntryService;
use App\Services\WebhookService;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function __construct(
        protected EntryService    $entryService,
        protected WebhookService  $webhookService,
    ) {}

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

        // i18n locale filter
        $currentLocale  = null;
        $locales        = [];
        $defaultLocale  = 'en';
        if ($ct->localized) {
            $locales       = json_decode(Setting::get('locales', '["en"]'), true) ?: ['en'];
            $defaultLocale = Setting::get('default_locale', 'en');
            $currentLocale = request('locale', $defaultLocale);
            if ($currentLocale !== 'all') {
                $query->where('locale', $currentLocale);
            }
        }

        $entries = $query->paginate(20)->withQueryString();
        $fields  = $ct->fields()->orderBy('sort_order')->get();

        // Get title field (first text/richtext field)
        $titleField = $fields->firstWhere('type', 'text') ?? $fields->firstWhere('type', 'richtext') ?? $fields->first();

        // Load title values for listing
        $titleValues = [];
        if ($titleField) {
            $entryIds  = $entries->pluck('id');
            $values    = \App\Models\EntryValue::whereIn('entry_id', $entryIds)
                ->where('field_id', $titleField->id)
                ->pluck('value_text', 'entry_id');
            $titleValues = $values->all();
        }

        return view('admin.entries.index', compact(
            'ct', 'entries', 'fields', 'titleField', 'titleValues',
            'locales', 'currentLocale', 'defaultLocale'
        ));
    }

    public function create(string $slug)
    {
        $ct     = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);
        $fields = $ct->fields()->orderBy('sort_order')->get();
        return view('admin.entries.edit', [
            'ct'     => $ct,
            'fields' => $fields,
            'entry'  => null,
            'values' => [],
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $ct = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);

        $status = $request->input('_status', 'draft');

        $entryData = [
            'content_type_id' => $ct->id,
            'status'          => in_array($status, ['draft', 'published']) ? $status : 'draft',
            'created_by'      => auth()->id(),
            'published_at'    => $status === 'published' ? now() : null,
        ];

        // i18n: set locale on creation
        if ($ct->localized) {
            $locale        = $request->input('_locale', Setting::get('default_locale', 'en'));
            $localeGroupId = (int) $request->input('_locale_group_id', 0);

            // Prevent duplicate locale in same group
            if ($localeGroupId) {
                $exists = Entry::where('locale_group_id', $localeGroupId)
                    ->where('locale', $locale)
                    ->exists();
                if ($exists) {
                    return back()->with('error', "A translation for '{$locale}' already exists in this group.");
                }
            }

            $entryData['locale']          = $locale;
            $entryData['locale_group_id'] = $localeGroupId ?: 0;
        }

        $entry = Entry::create($entryData);

        // Self-reference locale_group_id for the main (first) entry
        if ($ct->localized && empty($entryData['locale_group_id'])) {
            $entry->update(['locale_group_id' => $entry->id]);
        }

        $fields = $ct->fields()->orderBy('sort_order')->get();
        $this->entryService->saveValues(
            $entry,
            $request->except(['_token', '_method', '_status', '_locale', '_locale_group_id']),
            $fields
        );

        $this->webhookService->dispatch('entry.create', WebhookService::entryPayload(
            'entry.create', $ct->singular_name,
            ['id' => $entry->id, 'status' => $entry->status, 'createdAt' => $entry->created_at->toISOString()]
        ));

        return redirect()->route('admin.cm.edit', [$slug, $entry->id])
            ->with('success', 'Entry created.');
    }

    public function edit(string $slug, int $id)
    {
        $ct     = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);
        $entry  = Entry::where('content_type_id', $ct->id)->findOrFail($id);
        $fields = $ct->fields()->orderBy('sort_order')->get();
        $values = $this->entryService->getValues($entry);

        // i18n: load locale siblings for sidebar card
        $siblings      = [];
        $locales       = [];
        $defaultLocale = 'en';
        if ($ct->localized && $entry->locale_group_id) {
            $locales       = json_decode(Setting::get('locales', '["en"]'), true) ?: ['en'];
            $defaultLocale = Setting::get('default_locale', 'en');
            $siblings      = Entry::where('locale_group_id', $entry->locale_group_id)
                ->where('id', '!=', $entry->id)
                ->select('id', 'locale', 'status')
                ->get()
                ->keyBy('locale')
                ->toArray();
        }

        return view('admin.entries.edit', compact(
            'ct', 'entry', 'fields', 'values',
            'locales', 'siblings', 'defaultLocale'
        ));
    }

    public function update(Request $request, string $slug, int $id)
    {
        $ct    = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);
        $entry = Entry::where('content_type_id', $ct->id)->findOrFail($id);

        $oldStatus = $entry->status;
        $status    = $request->input('_status', $entry->status);
        $entry->update([
            'status'       => in_array($status, ['draft', 'published']) ? $status : $entry->status,
            'published_at' => ($status === 'published' && !$entry->published_at) ? now() : $entry->published_at,
        ]);

        $fields = $ct->fields()->orderBy('sort_order')->get();
        $this->entryService->saveValues(
            $entry,
            $request->except(['_token', '_method', '_status']),
            $fields
        );

        $entryData = ['id' => $entry->id, 'status' => $entry->status, 'updatedAt' => $entry->updated_at->toISOString()];
        if ($entry->status === 'published' && $oldStatus !== 'published') {
            $this->webhookService->dispatch('entry.publish', WebhookService::entryPayload('entry.publish', $ct->singular_name, $entryData));
        } elseif ($entry->status === 'draft' && $oldStatus === 'published') {
            $this->webhookService->dispatch('entry.unpublish', WebhookService::entryPayload('entry.unpublish', $ct->singular_name, $entryData));
        } else {
            $this->webhookService->dispatch('entry.update', WebhookService::entryPayload('entry.update', $ct->singular_name, $entryData));
        }

        return back()->with('success', 'Entry saved.');
    }

    public function destroy(string $slug, int $id)
    {
        $ct    = ContentType::findBySlug($slug);
        abort_if(!$ct, 404);
        $entry = Entry::where('content_type_id', $ct->id)->findOrFail($id);
        $entryData = ['id' => $entry->id, 'status' => $entry->status];
        $entry->delete();

        $this->webhookService->dispatch('entry.delete', WebhookService::entryPayload('entry.delete', $ct->singular_name, $entryData));

        if ($ct->isSingle()) {
            return redirect()->route('admin.cm.create', $slug)->with('success', 'Entry deleted.');
        }
        return redirect()->route('admin.cm.index', $slug)->with('success', 'Entry deleted.');
    }

    /**
     * Show create form pre-filled for translating an entry to a target locale.
     */
    public function translate(string $slug, int $id, string $locale)
    {
        $ct = ContentType::findBySlug($slug);
        abort_if(!$ct || !$ct->localized, 404);

        $source = Entry::where('content_type_id', $ct->id)->findOrFail($id);
        $fields = $ct->fields()->orderBy('sort_order')->get();

        // Pre-fill non-localizable field values from source entry
        $sourceValues  = $this->entryService->getValues($source);
        $prefillValues = [];
        foreach ($fields as $field) {
            if (!$field->isLocalizable()) {
                $prefillValues[$field->name] = $sourceValues[$field->name] ?? null;
            }
        }

        $localeGroupId = $source->locale_group_id ?: $source->id;

        return view('admin.entries.edit', [
            'ct'            => $ct,
            'entry'         => null,
            'fields'        => $fields,
            'values'        => $prefillValues,
            'targetLocale'  => $locale,
            'localeGroupId' => $localeGroupId,
            'locales'       => json_decode(Setting::get('locales', '["en"]'), true) ?: ['en'],
            'defaultLocale' => Setting::get('default_locale', 'en'),
            'siblings'      => [],
        ]);
    }
}
