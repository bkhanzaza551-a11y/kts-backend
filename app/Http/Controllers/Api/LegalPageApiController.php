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

        // Sanitize content before returning to mobile
        $content = $page->content ?? '';
        $content = strip_tags($content, '<p><br><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><a><blockquote>');
        $content = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $content);
        $content = preg_replace('/javascript\s*:/i', '', $content);

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $page->slug,
                'title' => $page->title,
                'content' => $content,
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
