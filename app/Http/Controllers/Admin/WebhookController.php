<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = Webhook::withCount('logs')->latest()->get();
        return view('admin.webhooks.index', compact('webhooks'));
    }

    public function create()
    {
        return view('admin.webhooks.form', ['webhook' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'url'        => ['required', 'url', 'max:500'],
            'events'     => ['required', 'array', 'min:1'],
            'events.*'   => ['string'],
            'headers'    => ['nullable', 'array'],
            'headers.*.key'   => ['nullable', 'string', 'max:100'],
            'headers.*.value' => ['nullable', 'string', 'max:500'],
        ]);

        // Remove blank header rows
        $headers = collect($data['headers'] ?? [])
            ->filter(fn($h) => !empty($h['key']))
            ->values()
            ->all();

        Webhook::create([
            'name'    => $data['name'],
            'url'     => $data['url'],
            'events'  => $data['events'],
            'headers' => $headers,
            'enabled' => true,
        ]);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook created.');
    }

    public function edit(int $id)
    {
        $webhook = Webhook::findOrFail($id);
        return view('admin.webhooks.form', compact('webhook'));
    }

    public function update(Request $request, int $id)
    {
        $webhook = Webhook::findOrFail($id);

        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'url'        => ['required', 'url', 'max:500'],
            'events'     => ['required', 'array', 'min:1'],
            'events.*'   => ['string'],
            'headers'    => ['nullable', 'array'],
            'headers.*.key'   => ['nullable', 'string', 'max:100'],
            'headers.*.value' => ['nullable', 'string', 'max:500'],
        ]);

        $headers = collect($data['headers'] ?? [])
            ->filter(fn($h) => !empty($h['key']))
            ->values()
            ->all();

        $webhook->update([
            'name'    => $data['name'],
            'url'     => $data['url'],
            'events'  => $data['events'],
            'headers' => $headers,
        ]);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook updated.');
    }

    public function toggle(int $id)
    {
        $webhook = Webhook::findOrFail($id);
        $webhook->update(['enabled' => !$webhook->enabled]);
        return response()->json(['enabled' => $webhook->enabled]);
    }

    public function destroy(int $id)
    {
        Webhook::findOrFail($id)->delete();
        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook deleted.');
    }

    public function logs(int $id)
    {
        $webhook = Webhook::findOrFail($id);
        $logs    = WebhookLog::where('webhook_id', $id)->latest()->paginate(20);
        return view('admin.webhooks.logs', compact('webhook', 'logs'));
    }
}
