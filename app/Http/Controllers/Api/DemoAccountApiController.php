<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemoAccountRequest;
use App\Models\DemoAccountSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DemoAccountApiController extends Controller
{
    public function instructions()
    {
        $settings = DemoAccountSetting::getSettings();

        return response()->json([
            'success' => true,
            'data' => [
                'title' => $settings->page_title,
                'description' => $settings->page_description,
                'referral_link' => $settings->referral_link,
                'steps' => $settings->instructions ?? DemoAccountSetting::getDefaultInstructions(),
                'account_types' => $settings->account_types ?? DemoAccountSetting::getDefaultAccountTypes(),
                'deposit_amounts' => $settings->deposit_amounts ?? ['1000', '5000', '10000', '50000', '100000'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $settings = DemoAccountSetting::getSettings();
        $validTypes = array_column($settings->account_types ?? [], 'value');

        $validated = $request->validate([
            'demo_email' => 'required_without:demo_phone|nullable|email|max:255',
            'demo_phone' => 'required_without:demo_email|nullable|string|max:20',
            'exness_account_number' => 'required|string|max:50',
            'account_type' => 'required|in:' . implode(',', $validTypes),
            'deposit_amount' => 'required',
            'user_notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        $existing = DemoAccountRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'linked'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active demo account request.',
                'data' => [
                    'request_id' => $existing->id,
                    'status' => $existing->status,
                ],
            ], 409);
        }

        $demoRequest = DemoAccountRequest::create([
            'user_id' => $user->id,
            'demo_email' => $validated['demo_email'] ?? null,
            'demo_phone' => $validated['demo_phone'] ?? null,
            'exness_account_number' => $validated['exness_account_number'],
            'account_type' => $validated['account_type'],
            'deposit_amount' => $validated['deposit_amount'],
            'user_notes' => $validated['user_notes'] ?? null,
            'status' => 'pending',
        ]);

        ActivityLogger::log(
            'create',
            'DemoAccountRequest',
            $demoRequest->id,
            "User submitted demo account request"
        );

        return response()->json([
            'success' => true,
            'message' => 'Demo account request submitted successfully. We will review it shortly.',
            'data' => [
                'request_id' => $demoRequest->id,
                'status' => $demoRequest->status,
                'created_at' => $demoRequest->created_at->toISOString(),
            ],
        ], 201);
    }

    public function myRequests(Request $request)
    {
        $user = $request->user();

        $requests = DemoAccountRequest::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'demo_email' => $req->demo_email,
                    'demo_phone' => $req->demo_phone,
                    'exness_account_number' => $req->exness_account_number,
                    'account_type' => $req->account_type,
                    'deposit_amount' => $req->deposit_amount,
                    'status' => $req->status,
                    'admin_notes' => $req->admin_notes,
                    'created_at' => $req->created_at->toISOString(),
                    'reviewed_at' => $req->reviewed_at?->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function show(Request $request, DemoAccountRequest $demoRequest)
    {
        if ($demoRequest->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $demoRequest->id,
                'demo_email' => $demoRequest->demo_email,
                'demo_phone' => $demoRequest->demo_phone,
                'exness_account_number' => $demoRequest->exness_account_number,
                'account_type' => $demoRequest->account_type,
                'deposit_amount' => $demoRequest->deposit_amount,
                'status' => $demoRequest->status,
                'admin_notes' => $demoRequest->admin_notes,
                'user_notes' => $demoRequest->user_notes,
                'created_at' => $demoRequest->created_at->toISOString(),
                'reviewed_at' => $demoRequest->reviewed_at?->toISOString(),
            ],
        ]);
    }
}
