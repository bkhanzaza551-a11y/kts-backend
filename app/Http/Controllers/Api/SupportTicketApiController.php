<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportTicketApiController extends Controller
{
    public function index(Request $request)
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->withCount('replies')
            ->latest()
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high'
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-' . strtoupper(Str::random(8)),
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully',
            'data' => $ticket
        ]);
    }

    public function show(Request $request, $id)
    {
        $ticket = SupportTicket::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
            
        $replies = SupportTicketReply::where('support_ticket_id', $ticket->id)
            ->with('user:id,name,role') // assuming role or similar exists, but let's just get name
            ->oldest()
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => $ticket,
                'replies' => $replies
            ]
        ]);
    }

    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'message' => 'required|string'
        ]);

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $validated['message']
        ]);

        // Optional: Update ticket status to open if it was pending or resolved and user replied
        if ($ticket->status !== 'open') {
            $ticket->update(['status' => 'open']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reply sent successfully',
            'data' => $reply
        ]);
    }
}
