<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoAccountSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class DemoAccountSettingsController extends Controller
{
    public function index()
    {
        $settings = DemoAccountSetting::getSettings();
        return view('admin.demo-accounts.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'referral_link' => 'nullable|url|max:500',
            'page_title' => 'required|string|max:255',
            'page_description' => 'nullable|string|max:1000',
            'instructions' => 'required|array|min:1',
            'instructions.*.step' => 'required|integer',
            'instructions.*.title' => 'required|string|max:255',
            'instructions.*.description' => 'required|string|max:500',
            'instructions.*.url' => 'nullable|url|max:500',
            'account_types' => 'required|array|min:1',
            'account_types.*.value' => 'required|string|max:50',
            'account_types.*.label' => 'required|string|max:100',
            'account_types.*.description' => 'nullable|string|max:255',
            'deposit_amounts' => 'required|array|min:1',
            'deposit_amounts.*' => 'required|string|max:10',
        ]);

        $settings = DemoAccountSetting::getSettings();
        $settings->update([
            'referral_link' => $validated['referral_link'] ?? null,
            'page_title' => $validated['page_title'],
            'page_description' => $validated['page_description'] ?? null,
            'instructions' => $validated['instructions'],
            'account_types' => $validated['account_types'],
            'deposit_amounts' => $validated['deposit_amounts'],
        ]);

        ActivityLogger::log(
            'update',
            'DemoAccountSetting',
            $settings->id,
            'Updated demo account settings'
        );

        return redirect()->route('admin.demo-settings.index')
            ->with('success', 'Demo account settings updated successfully.');
    }
}
