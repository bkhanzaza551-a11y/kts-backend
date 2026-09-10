<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EducationCategory;
use Illuminate\Http\JsonResponse;

class EducationApiController extends Controller
{
    public function courses(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Course::with('category')
            ->withCount(['lessons' => fn($q) => $q->where('is_published', true)])
            ->where('is_published', true);

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($difficulty = $request->input('difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $courses = $query->latest()->paginate(15);

        return response()->json(['success' => true, 'data' => $courses]);
    }

    public function course($id): JsonResponse
    {
        $course = Course::with([
            'category',
            'lessons' => fn($q) => $q->where('is_published', true)->orderBy('sort_order', 'asc')
        ])
        ->where('is_published', true)
        ->findOrFail($id);

        // Increment views count safely
        $course->increment('views_count');

        return response()->json(['success' => true, 'data' => $course]);
    }

    public function categories(): JsonResponse
    {
        $categories = EducationCategory::where('is_active', true)
            ->withCount(['courses' => fn($q) => $q->where('is_published', true)])
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }
}
