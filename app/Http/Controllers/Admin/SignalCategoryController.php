<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SignalCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SignalCategoryController extends Controller
{
    public function index()
    {
        $categories = SignalCategory::withCount('signals')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.signals.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.signals.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:signal_categories,name',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = SignalCategory::create($validated);

        ActivityLogger::log('create', 'SignalCategory', $category->id, "Created signal category: {$validated['name']}");

        return redirect()->route('admin.signal-categories.index')->with('success', 'Category created successfully.');
    }

    public function show(SignalCategory $signalCategory)
    {
        $signalCategory->loadCount('signals');

        return view('admin.signals.categories.show', compact('signalCategory'));
    }

    public function edit(SignalCategory $signalCategory)
    {
        $signalCategory->loadCount('signals');

        return view('admin.signals.categories.edit', ['category' => $signalCategory]);
    }

    public function update(Request $request, SignalCategory $signalCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:signal_categories,name,' . $signalCategory->id,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|string|max:7',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $oldName = $signalCategory->name;
        $signalCategory->update($validated);

        ActivityLogger::log('update', 'SignalCategory', $signalCategory->id, "Updated signal category: {$oldName}");

        return redirect()->route('admin.signal-categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(SignalCategory $signalCategory)
    {
        if ($signalCategory->signals()->count() > 0) {
            return back()->with('error', 'Cannot delete category with associated signals. Remove signals from this category first.');
        }

        $id = $signalCategory->id;
        $name = $signalCategory->name;
        $signalCategory->delete();

        ActivityLogger::log('delete', 'SignalCategory', $id, "Deleted signal category: {$name}");

        return redirect()->route('admin.signal-categories.index')->with('success', 'Category deleted successfully.');
    }
}
