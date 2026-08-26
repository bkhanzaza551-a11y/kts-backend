<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class LegalPageController extends Controller
{
    public function index()
    {
        $pages = LegalPage::with('editor')->latest()->get();
        return view('admin.legal-pages.index', compact('pages'));
    }

    public function edit(string $slug)
    {
        $page = LegalPage::where('slug', $slug)->firstOrFail();
        return view('admin.legal-pages.edit', compact('page'));
    }

    public function update(Request $request, string $slug)
    {
        $page = LegalPage::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $page->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'summary' => $validated['summary'] ?? null,
            'is_active' => isset($validated['is_active']),
            'last_edited_by' => auth()->id(),
        ]);

        ActivityLogger::log(
            'update',
            'LegalPage',
            $page->id,
            "Updated legal page: {$page->title}"
        );

        return redirect()->route('admin.legal-pages.edit', $slug)
            ->with('success', 'Legal page updated successfully.');
    }

    public function publish(string $slug)
    {
        $page = LegalPage::where('slug', $slug)->firstOrFail();

        $page->update([
            'is_active' => true,
            'last_published_at' => now(),
            'last_edited_by' => auth()->id(),
        ]);

        ActivityLogger::log(
            'publish',
            'LegalPage',
            $page->id,
            "Published legal page: {$page->title}"
        );

        return redirect()->route('admin.legal-pages.edit', $slug)
            ->with('success', 'Legal page published successfully.');
    }

    public function create()
    {
        return view('admin.legal-pages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:100|unique:legal_pages,slug',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'summary' => 'nullable|string|max:500',
        ]);

        $page = LegalPage::create([
            'slug' => $validated['slug'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'summary' => $validated['summary'] ?? null,
            'is_active' => false,
            'last_edited_by' => auth()->id(),
        ]);

        ActivityLogger::log(
            'create',
            'LegalPage',
            $page->id,
            "Created legal page: {$page->title}"
        );

        return redirect()->route('admin.legal-pages.edit', $page->slug)
            ->with('success', 'Legal page created successfully.');
    }

    public function destroy(string $slug)
    {
        $page = LegalPage::where('slug', $slug)->firstOrFail();
        $page->delete();

        ActivityLogger::log(
            'delete',
            'LegalPage',
            $page->id,
            "Deleted legal page: {$page->title}"
        );

        return redirect()->route('admin.legal-pages.index')
            ->with('success', 'Legal page deleted.');
    }
}
