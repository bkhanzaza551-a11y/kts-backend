<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::with(['user:id,name,email', 'replies']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = trim($request->input('search', ''))) {
            $safeSearch = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($safeSearch) {
                $q->where('subject', 'like', "%{$safeSearch}%")
                    ->orWhere('ticket_number', 'like', "%{$safeSearch}%")
                    ->orWhereHas('user', function ($uq) use ($safeSearch) {
                        $uq->where('name', 'like', "%{$safeSearch}%")
                            ->orWhere('email', 'like', "%{$safeSearch}%");
                    });
            });
        }

        $tickets = $query->latest()->paginate(20);

        return view('admin.support-chat.index', compact('tickets'));
    }

    public function show(Request $request, SupportTicket $ticket)
    {
        $ticket->load(['user:id,name,email,phone', 'replies.user:id,name']);

        $replies = $ticket->replies()->oldest()->get();

        return view('admin.support-chat.show', compact('ticket', 'replies'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,mp4',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . \Illuminate\Support\Str::random(10) . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('support-chat', $filename, 'public');
        }

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
        ]);

        if ($ticket->status !== 'open') {
            $ticket->update(['status' => 'open']);
        }

        return redirect()->back()->with('success', 'Reply sent successfully.');
    }

    public function close(Request $request, SupportTicket $ticket)
    {
        $ticket->update(['status' => 'closed']);

        return redirect()->back()->with('success', 'Ticket closed successfully.');
    }

    public function reopen(Request $request, SupportTicket $ticket)
    {
        $ticket->update(['status' => 'open']);

        return redirect()->back()->with('success', 'Ticket reopened successfully.');
    }
}
