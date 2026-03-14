<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\ContentType;
use App\Models\Entry;
use App\Models\Media;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'content_types' => ContentType::count(),
            'entries'       => Entry::count(),
            'media'         => Media::count(),
            'api_tokens'    => ApiToken::count(),
        ];

        $contentTypes   = ContentType::orderBy('sort_order')->get();
        $recentEntries  = Entry::with(['contentType', 'creator'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'contentTypes', 'recentEntries'));
    }
}
