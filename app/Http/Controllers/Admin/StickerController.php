<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatSticker;
use App\Models\ChatStickerPack;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StickerController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatStickerPack::withCount(['stickers', 'stickers as active_stickers_count' => function ($q) {
            $q->where('is_active', true);
        }]);

        if ($search = trim($request->input('search', ''))) {
            $safeSearch = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($safeSearch) {
                $q->where('name', 'like', "%{$safeSearch}%")
                  ->orWhere('description', 'like', "%{$safeSearch}%");
            });
        }

        if ($request->input('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->input('status') === 'inactive') {
            $query->where('is_active', false);
        }

        $packs = $query->orderBy('sort_order')->paginate(20)->withQueryString();

        $stats = [
            'total_packs' => ChatStickerPack::count(),
            'active_packs' => ChatStickerPack::where('is_active', true)->count(),
            'total_stickers' => ChatSticker::count(),
            'active_stickers' => ChatSticker::where('is_active', true)->count(),
            'total_usage' => (int) ChatSticker::sum('usage_count'),
            'most_used' => ChatSticker::with('pack')->orderByDesc('usage_count')->first(),
        ];

        return view('admin.chat.stickers.index', compact('packs', 'stats'));
    }

    public function createPack()
    {
        return view('admin.chat.stickers.create-pack');
    }

    public function storePack(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $slug = Str::slug($validated['name']);
        if (ChatStickerPack::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(5);
        }
        $validated['slug'] = $slug;
        $validated['created_by'] = auth()->id();
        $validated['sort_order'] = (int) ChatStickerPack::max('sort_order') + 1;

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('stickers/packs', 'public');
        }

        ChatStickerPack::create($validated);

        ActivityLogger::log('chat_sticker_pack_create', 'ChatStickerPack', null, "Created sticker pack: {$validated['name']}");

        return redirect()->route('admin.chat.stickers.index')->with('success', 'Sticker pack created.');
    }

    public function editPack(ChatStickerPack $pack)
    {
        $pack->loadCount('stickers');
        return view('admin.chat.stickers.edit-pack', compact('pack'));
    }

    public function updatePack(Request $request, ChatStickerPack $pack)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'thumbnail' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($pack->thumbnail) {
                Storage::disk('public')->delete($pack->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('stickers/packs', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? $pack->sort_order);

        $pack->update($validated);

        ActivityLogger::log('chat_sticker_pack_update', 'ChatStickerPack', $pack->id, "Updated sticker pack: {$pack->name}");

        return redirect()->route('admin.chat.stickers.index')->with('success', 'Sticker pack updated.');
    }

    public function destroyPack(ChatStickerPack $pack)
    {
        foreach ($pack->stickers as $sticker) {
            if ($sticker->image_url) {
                Storage::disk('public')->delete($sticker->image_url);
            }
        }
        if ($pack->thumbnail) {
            Storage::disk('public')->delete($pack->thumbnail);
        }

        $pack->delete();
        Cache::forget('active_sticker_packs');

        ActivityLogger::log('chat_sticker_pack_delete', 'ChatStickerPack', null, "Deleted sticker pack: {$pack->name}");

        return back()->with('success', 'Sticker pack deleted.');
    }

    public function togglePack(ChatStickerPack $pack)
    {
        $pack->update(['is_active' => !$pack->is_active]);
        Cache::forget('active_sticker_packs');

        return back()->with('success', $pack->is_active ? 'Pack activated.' : 'Pack deactivated.');
    }

    public function showPack(ChatStickerPack $pack)
    {
        $stickers = $pack->stickers()->orderBy('sort_order')->paginate(30);
        return view('admin.chat.stickers.pack-show', compact('pack', 'stickers'));
    }

    public function uploadSticker(Request $request)
    {
        $validated = $request->validate([
            'pack_id' => 'required|exists:chat_sticker_packs,id',
            'name' => 'required|string|max:255',
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'required|image|mimes:png,jpg,jpeg,gif,webp|max:20480',
        ]);

        $pack = ChatStickerPack::findOrFail($validated['pack_id']);
        $baseSort = (int) $pack->stickers()->max('sort_order') + 1;

        $count = 0;
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('stickers/' . $pack->slug, 'public');
            $sizeKb = round($image->getSize() / 1024, 1);

            ChatSticker::create([
                'pack_id' => $pack->id,
                'name' => $validated['name'] . ($count > 0 ? ' ' . ($count + 1) : ''),
                'image_url' => $path,
                'file_size' => "{$sizeKb}KB",
                'is_active' => true,
                'sort_order' => $baseSort + $index,
            ]);
            $count++;
        }

        ActivityLogger::log('chat_sticker_upload', 'ChatSticker', null, "Uploaded {$count} stickers to pack: {$pack->name}");

        return back()->with('success', "{$count} sticker(s) uploaded successfully.");
    }

    public function destroySticker(ChatSticker $sticker)
    {
        if ($sticker->image_url) {
            Storage::disk('public')->delete($sticker->image_url);
        }

        $pack = $sticker->pack;
        $sticker->delete();

        ActivityLogger::log('chat_sticker_delete', 'ChatSticker', $sticker->id, "Deleted sticker from pack: {$pack->name}");

        return back()->with('success', 'Sticker deleted.');
    }

    public function toggleSticker(ChatSticker $sticker)
    {
        $sticker->update(['is_active' => !$sticker->is_active]);
        return back()->with('success', $sticker->is_active ? 'Sticker activated.' : 'Sticker deactivated.');
    }

    public function destroySelected(Request $request)
    {
        $validated = $request->validate([
            'sticker_ids' => 'required|array',
            'sticker_ids.*' => 'exists:chat_stickers,id',
        ]);

        $stickers = ChatSticker::whereIn('id', $validated['sticker_ids'])->get();
        foreach ($stickers as $sticker) {
            if ($sticker->image_url) {
                Storage::disk('public')->delete($sticker->image_url);
            }
            $sticker->delete();
        }

        return back()->with('success', count($stickers) . ' sticker(s) deleted.');
    }
}
