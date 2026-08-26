<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;

class LegalPageApiController extends Controller
{
    public function show(string $slug)
    {
        $page = LegalPage::active()->bySlug($slug)->first();

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $page->slug,
                'title' => $page->title,
                'content' => $page->content,
                'summary' => $page->summary,
                'last_updated' => $page->updated_at->toISOString(),
            ],
        ]);
    }

    public function list()
    {
        $pages = LegalPage::active()
            ->select('slug', 'title', 'summary', 'updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }
}
