<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSticker;
use App\Models\ChatStickerPack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StickerApiController extends Controller
{
    public function index()
    {
        $packs = Cache::remember('active_sticker_packs_api', 600, function () {
            return ChatStickerPack::where('is_active', true)
                ->with(['stickers' => function ($q) {
                    $q->where('is_active', true)->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get()
                ->map(function ($pack) {
                    return [
                        'id' => $pack->id,
                        'name' => $pack->name,
                        'slug' => $pack->slug,
                        'description' => $pack->description,
                        'thumbnail' => $pack->thumbnail ? asset('storage/' . $pack->thumbnail) : null,
                        'stickers' => $pack->stickers->map(function ($sticker) {
                            return [
                                'id' => $sticker->id,
                                'name' => $sticker->name,
                                'image_url' => asset('storage/' . $sticker->image_url),
                            ];
                        }),
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'data' => $packs,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sticker_id' => 'required|exists:chat_stickers,id',
        ]);

        $sticker = ChatSticker::with('pack')->find($request->sticker_id);
        $sticker->incrementUsage();

        Cache::forget('active_sticker_packs_api');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sticker->id,
                'name' => $sticker->name,
                'image_url' => asset('storage/' . $sticker->image_url),
                'pack_name' => $sticker->pack->name,
            ],
        ]);
    }
}
