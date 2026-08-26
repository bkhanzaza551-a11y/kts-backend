<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EducationCategoryController extends Controller
{
    public function index()
    {
        $categories = EducationCategory::withCount('courses')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.education.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.education.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:education_categories,name',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = EducationCategory::create($validated);

        ActivityLogger::log('create', 'EducationCategory', $category->id, "Created education category: {$validated['name']}", null, $validated);
        Cache::forget('education_stats');

        return redirect()->route('admin.education-categories.index')->with('success', 'Category created successfully.');
    }

    public function show(EducationCategory $educationCategory)
    {
        $educationCategory->loadCount('courses');
        $courses = $educationCategory->courses()->with('creator')->withCount('lessons')->latest()->paginate(15);

        return view('admin.education.categories.show', ['category' => $educationCategory, 'courses' => $courses]);
    }

    public function edit(EducationCategory $educationCategory)
    {
        $educationCategory->loadCount('courses');

        return view('admin.education.categories.edit', ['category' => $educationCategory]);
    }

    public function update(Request $request, EducationCategory $educationCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:education_categories,name,' . $educationCategory->id,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $oldValues = $educationCategory->only(['name', 'description', 'icon', 'color', 'sort_order', 'is_active']);
        $educationCategory->update($validated);
        $newValues = $educationCategory->only(array_keys($oldValues));

        ActivityLogger::log('update', 'EducationCategory', $educationCategory->id, "Updated education category: {$educationCategory->name}", $oldValues, $newValues);
        Cache::forget('education_stats');

        return redirect()->route('admin.education-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(EducationCategory $educationCategory)
    {
        $result = DB::transaction(function () use ($educationCategory) {
            if ($educationCategory->courses()->count() > 0) {
                return ['success' => false, 'message' => 'Cannot delete category with associated courses. Remove courses first.'];
            }

            $id = $educationCategory->id;
            $oldValues = $educationCategory->only(['name', 'color', 'sort_order', 'is_active']);
            $educationCategory->delete();

            ActivityLogger::log('delete', 'EducationCategory', $id, "Deleted education category: {$oldValues['name']}", $oldValues, null);
            Cache::forget('education_stats');

            return ['success' => true];
        });

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('admin.education-categories.index')->with('success', 'Category deleted successfully.');
    }

    public function restore(EducationCategory $educationCategory)
    {
        if (!$educationCategory->trashed()) {
            return back()->with('error', 'Category is not deleted.');
        }

        $educationCategory->restore();
        $educationCategory->update(['is_active' => true]);

        ActivityLogger::log('restore', 'EducationCategory', $educationCategory->id, "Restored education category: {$educationCategory->name}");
        Cache::forget('education_stats');

        return redirect()->route('admin.education-categories.index')->with('success', 'Category restored successfully.');
    }
}
