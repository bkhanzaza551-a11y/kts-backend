<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EducationCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EducationController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['category', 'creator'])->withCount('lessons');

        if ($search = trim($request->input('search', ''))) {
            $safeSearch = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($safeSearch) {
                $q->where('title', 'like', "%{$safeSearch}%")
                  ->orWhere('description', 'like', "%{$safeSearch}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            if (is_numeric($categoryId)) {
                $query->where('category_id', $categoryId);
            }
        }

        if ($difficulty = $request->input('difficulty')) {
            if (in_array($difficulty, ['beginner', 'intermediate', 'advanced'])) {
                $query->where('difficulty', $difficulty);
            }
        }

        if ($request->has('is_published') && $request->input('is_published') !== '') {
            $query->where('is_published', $request->boolean('is_published'));
        }

        if ($request->has('is_featured') && $request->input('is_featured') !== '') {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        if ($request->filled('date_from') && $this->isValidDate($request->input('date_from'))) {
            $query->where('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to') && $this->isValidDate($request->input('date_to'))) {
            $query->where('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $sort = $request->input('sort', 'created_at');
        $dir = $request->input('dir', 'desc');
        $allowedSorts = ['id', 'title', 'difficulty', 'is_published', 'is_featured', 'views_count', 'enrollments_count', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $courses = $query->paginate(15)->withQueryString();
        $categories = EducationCategory::where('is_active', true)->orderBy('name')->get();

        $stats = Cache::remember('education_stats', 60, function () {
            $row = DB::table('courses')
                ->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN is_published = 0 THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
                    COALESCE(SUM(views_count), 0) as total_views,
                    COALESCE(SUM(enrollments_count), 0) as total_enrollments
                ")->first();

            $lessonsCount = DB::table('lessons')->whereNull('deleted_at')->count();

            return [
                'total' => (int) $row->total,
                'published' => (int) $row->published,
                'draft' => (int) $row->draft,
                'featured' => (int) $row->featured,
                'total_views' => (int) $row->total_views,
                'total_enrollments' => (int) $row->total_enrollments,
                'total_lessons' => $lessonsCount,
            ];
        });

        return view('admin.education.index', compact('courses', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = EducationCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.education.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'category_id' => 'required|exists:education_categories,id',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'estimated_hours' => 'nullable|integer|min:0|max:10000',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);

        $category = EducationCategory::find($validated['category_id']);
        if (!$category || !$category->is_active) {
            return back()->withErrors(['category_id' => 'Selected category is not active.'])->withInput();
        }

        $validated['created_by'] = auth()->id();
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');

        if ($validated['is_published']) {
            $validated['published_at'] = now();
        }

        $course = Course::create($validated);

        ActivityLogger::log('create', 'Course', $course->id, "Created course: {$course->title}", null, $course->toArray());
        Cache::forget('education_stats');

        return redirect()->route('admin.courses.show', $course)->with('success', 'Course created successfully.');
    }

    public function show(Course $course)
    {
        $course->load(['category', 'creator', 'lessons']);
        $lessonsCount = $course->lessons->count();
        $publishedLessonsCount = $course->lessons->where('is_published', true)->count();
        $totalDuration = $course->lessons->sum('duration_minutes');

        return view('admin.education.show', compact('course', 'lessonsCount', 'publishedLessonsCount', 'totalDuration'));
    }

    public function edit(Course $course)
    {
        $categories = EducationCategory::where('is_active', true)->orderBy('name')->get();
        return view('admin.education.edit', compact('course', 'categories'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'category_id' => 'required|exists:education_categories,id',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'estimated_hours' => 'nullable|integer|min:0|max:10000',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');

        $category = EducationCategory::find($validated['category_id']);
        if (!$category || !$category->is_active) {
            return back()->withErrors(['category_id' => 'Selected category is not active.'])->withInput();
        }

        if ($validated['is_published'] && !$course->is_published) {
            $validated['published_at'] = now();
        } elseif (!$validated['is_published'] && $course->is_published) {
            $validated['published_at'] = null;
        }

        $oldValues = $course->only(['title', 'description', 'category_id', 'difficulty', 'estimated_hours', 'is_featured', 'is_published']);
        $course->update($validated);
        $newValues = $course->only(array_keys($oldValues));

        ActivityLogger::log('update', 'Course', $course->id, "Updated course: {$course->title}", $oldValues, $newValues);
        Cache::forget('education_stats');

        return redirect()->route('admin.courses.show', $course)->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $title = $course->title;
        $oldValues = $course->only(['title', 'difficulty', 'is_published']);

        DB::transaction(function () use ($course) {
            foreach ($course->lessons()->withTrashed()->get() as $lesson) {
                $lesson->delete();
            }
            $course->delete();
        });

        ActivityLogger::log('delete', 'Course', $course->id, "Deleted course: {$title}", $oldValues, null);
        Cache::forget('education_stats');

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }

    public function restore(Course $course)
    {
        if (!$course->trashed()) {
            return back()->with('error', 'Course is not deleted.');
        }

        DB::transaction(function () use ($course) {
            $course->restore();
            $course->lessons()->onlyTrashed()->restore();
        });

        ActivityLogger::log('restore', 'Course', $course->id, "Restored course: {$course->title}");
        Cache::forget('education_stats');

        return redirect()->route('admin.courses.show', $course)->with('success', 'Course restored successfully.');
    }

    public function publish(Course $course)
    {
        if (!$course->publish()) {
            return back()->with('error', 'Course is already published.');
        }

        ActivityLogger::log('publish', 'Course', $course->id, "Published course: {$course->title}", ['is_published' => false], ['is_published' => true]);
        Cache::forget('education_stats');

        return back()->with('success', 'Course published successfully.');
    }

    public function unpublish(Course $course)
    {
        if (!$course->unpublish()) {
            return back()->with('error', 'Course is not published.');
        }

        ActivityLogger::log('unpublish', 'Course', $course->id, "Unpublished course: {$course->title}", ['is_published' => true], ['is_published' => false]);
        Cache::forget('education_stats');

        return back()->with('success', 'Course unpublished successfully.');
    }

    private function isValidDate(string $date): bool
    {
        return strtotime($date) !== false;
    }
}
