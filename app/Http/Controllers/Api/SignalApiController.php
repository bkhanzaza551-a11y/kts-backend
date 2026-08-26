<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SignalApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $signals = Signal::with('categories:id,name,slug,color')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $signals,
        ]);
    }

    public function latest(): JsonResponse
    {
        $signals = Signal::where('status', 'active')
            ->with('categories:id,name,slug,color')
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $signals,
        ]);
    }

    public function show(Signal $signal): JsonResponse
    {
        $signal->load('categories:id,name,slug,color');

        return response()->json([
            'success' => true,
            'data' => $signal,
        ]);
    }

    public function closed(): JsonResponse
    {
        $signals = Signal::where('status', 'closed')
            ->with('categories:id,name,slug,color')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $signals,
        ]);
    }
}
