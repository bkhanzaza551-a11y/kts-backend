<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemoAccountSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_link',
        'page_title',
        'page_description',
        'instructions',
        'account_types',
        'deposit_amounts',
        'is_active',
    ];

    protected $casts = [
        'instructions' => 'array',
        'account_types' => 'array',
        'deposit_amounts' => 'array',
        'is_active' => 'boolean',
    ];

    public static function getSettings(): self
    {
        return static::firstOrCreate([], [
            'referral_link' => 'https://www.exness.com/register/',
            'page_title' => 'How to Create Exness Demo Account',
            'page_description' => 'Follow these simple steps to create your Exness demo account and start trading with KTS 10 Pips Bots.',
            'instructions' => self::getDefaultInstructions(),
            'account_types' => self::getDefaultAccountTypes(),
            'deposit_amounts' => ['1000', '5000', '10000', '50000', '100000'],
        ]);
    }

    public static function getDefaultInstructions(): array
    {
        return [
            ['step' => 1, 'title' => 'Register on Exness', 'description' => 'Go to exness.com/register and create a free account with your email.', 'url' => 'https://www.exness.com/register/'],
            ['step' => 2, 'title' => 'Verify Your Account', 'description' => 'Verify your email address and phone number for full access.'],
            ['step' => 3, 'title' => 'Open Demo Account', 'description' => 'Go to Accounts → Open Account → Select Demo Account.'],
            ['step' => 4, 'title' => 'Choose Account Type', 'description' => 'Select Standard or Pro account. Set leverage to 1:2000 for best experience.'],
            ['step' => 5, 'title' => 'Set Deposit Amount', 'description' => 'We recommend $10,000 demo balance for practice.'],
            ['step' => 6, 'title' => 'Copy Account Number', 'description' => 'Your account number is shown in the Exness dashboard under Accounts.'],
        ];
    }

    public static function getDefaultAccountTypes(): array
    {
        return [
            ['value' => 'standard', 'label' => 'Standard', 'description' => 'Best for beginners. No commissions.'],
            ['value' => 'pro', 'label' => 'Pro', 'description' => 'Tight spreads. For experienced traders.'],
            ['value' => 'raw', 'label' => 'Raw Spread', 'description' => 'Lowest spreads + commission.'],
        ];
    }
}
