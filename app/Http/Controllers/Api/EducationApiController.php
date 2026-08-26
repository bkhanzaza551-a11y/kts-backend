<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\EducationCategory;
use Illuminate\Http\JsonResponse;

class EducationApiController extends Controller
{
    public function courses(): JsonResponse
    {
        $courses = Course::with('category', 'lessons')
            ->where('is_published', true)
            ->latest()
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $courses]);
    }

    public function course($id): JsonResponse
    {
        $course = Course::with(['category', 'lessons'])
            ->where('is_published', true)
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $course]);
    }

    public function categories(): JsonResponse
    {
        $categories = EducationCategory::where('is_active', true)
            ->withCount('courses')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    }
}
