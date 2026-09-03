<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    public function index(Request $request, Course $course)
    {
        $course->load('category');
        $lessons = $course->lessons()->paginate(15);

        return view('admin.education.lessons.index', compact('course', 'lessons'));
    }

    public function create(Course $course)
    {
        $maxSort = $course->lessons()->max('sort_order') ?? 0;

        return view('admin.education.lessons.create', ['course' => $course, 'nextSort' => $maxSort + 1]);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'nullable|string|max:50000',
            'video_url' => 'nullable|string|max:255',
            'duration_minutes' => 'nullable|integer|min:0|max:10000',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        if (!empty($validated['video_url']) && !$this->isValidVideoUrl($validated['video_url'])) {
            return back()->withErrors(['video_url' => 'Please enter a valid video URL (YouTube, Vimeo, or standard URL).'])->withInput();
        }

        $validated['course_id'] = $course->id;
        $validated['is_published'] = $request->boolean('is_published');

        $lesson = DB::transaction(function () use ($validated, $course) {
            $maxSort = $course->lessons()->whereNull('deleted_at')->max('sort_order') ?? 0;
            $validated['sort_order'] = $validated['sort_order'] ?? $maxSort + 1;
            return Lesson::create($validated);
        });

        ActivityLogger::log('create', 'Lesson', $lesson->id, "Created lesson: {$lesson->title} in course: {$course->title}", null, $validated);
        Cache::forget('education_stats');

        return redirect()->route('admin.courses.lessons.show', [$course, $lesson])->with('success', 'Lesson created successfully.');
    }

    private function verifyLessonBelongsToCourse(Course $course, Lesson $lesson): bool
    {
        return $lesson->course_id === $course->id;
    }

    public function show(Course $course, Lesson $lesson)
    {
        if (!$this->verifyLessonBelongsToCourse($course, $lesson)) {
            abort(404);
        }

        $nextLesson = $lesson->getNextLesson();
        $prevLesson = $lesson->getPreviousLesson();

        return view('admin.education.lessons.show', compact('course', 'lesson', 'nextLesson', 'prevLesson'));
    }

    public function edit(Course $course, Lesson $lesson)
    {
        if (!$this->verifyLessonBelongsToCourse($course, $lesson)) {
            abort(404);
        }

        return view('admin.education.lessons.edit', compact('course', 'lesson'));
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        if (!$this->verifyLessonBelongsToCourse($course, $lesson)) {
            abort(404);
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'content' => 'nullable|string|max:50000',
            'video_url' => 'nullable|string|max:255',
            'duration_minutes' => 'nullable|integer|min:0|max:10000',
            'sort_order' => 'nullable|integer|min:0',
            'is_published' => 'boolean',
        ]);

        if (!empty($validated['video_url']) && !$this->isValidVideoUrl($validated['video_url'])) {
            return back()->withErrors(['video_url' => 'Please enter a valid video URL (YouTube, Vimeo, or standard URL).'])->withInput();
        }

        $validated['is_published'] = $request->boolean('is_published');

        $oldValues = $lesson->only(['title', 'description', 'content', 'video_url', 'duration_minutes', 'sort_order', 'is_published']);
        $lesson->update($validated);
        $newValues = $lesson->only(array_keys($oldValues));

        ActivityLogger::log('update', 'Lesson', $lesson->id, "Updated lesson: {$lesson->title}", $oldValues, $newValues);
        Cache::forget('education_stats');

        return redirect()->route('admin.courses.lessons.show', [$course, $lesson])->with('success', 'Lesson updated successfully.');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        if (!$this->verifyLessonBelongsToCourse($course, $lesson)) {
            abort(404);
        }

        $title = $lesson->title;
        $oldValues = $lesson->only(['title', 'sort_order', 'is_published']);
        $lesson->delete();

        ActivityLogger::log('delete', 'Lesson', $lesson->id, "Deleted lesson: {$title} from course: {$course->title}", $oldValues, null);
        Cache::forget('education_stats');

        return redirect()->route('admin.courses.lessons.index', $course)->with('success', 'Lesson deleted successfully.');
    }

    public function restore(Course $course, Lesson $lesson)
    {
        if (!$lesson->trashed()) {
            return back()->with('error', 'Lesson is not deleted.');
        }

        $lesson->restore();

        ActivityLogger::log('restore', 'Lesson', $lesson->id, "Restored lesson: {$lesson->title} in course: {$course->title}");
        Cache::forget('education_stats');

        return redirect()->route('admin.courses.lessons.show', [$course, $lesson])->with('success', 'Lesson restored successfully.');
    }

    public function reorder(Request $request, Course $course)
    {
        $validated = $request->validate([
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:lessons,id',
        ]);

        $lessonIds = $validated['lesson_ids'];

        $ownedCount = Lesson::where('course_id', $course->id)
            ->whereIn('id', $lessonIds)
            ->count();

        if ($ownedCount !== count($lessonIds)) {
            return back()->with('error', 'Some lessons do not belong to this course.');
        }

        DB::transaction(function () use ($lessonIds, $course) {
            $allLessons = Lesson::where('course_id', $course->id)->orderBy('sort_order')->get();
            $reorderedLessons = $allLessons->sortBy(function ($lesson) use ($lessonIds) {
                $index = array_search($lesson->id, $lessonIds);
                return $index !== false ? $index : count($lessonIds) + $lesson->sort_order;
            })->values();

            foreach ($reorderedLessons as $index => $lesson) {
                if ($lesson->sort_order !== $index + 1) {
                    $lesson->update(['sort_order' => $index + 1]);
                }
            }
        });

        ActivityLogger::log('reorder', 'Lesson', null, "Reordered lessons in course: {$course->title}");
        Cache::forget('education_stats');

        return back()->with('success', 'Lesson order updated.');
    }

    private function isValidVideoUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return false;
        }
        $allowed = ['youtube.com', 'www.youtube.com', 'youtu.be', 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com', 'dailymotion.com', 'www.dailymotion.com', 'streamable.com'];
        foreach ($allowed as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }
        return false;
    }
}
