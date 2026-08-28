<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'app_name', 'value' => 'KTS Markets', 'type' => 'text', 'description' => 'Application name', 'group' => 'general'],
            ['key' => 'support_email', 'value' => 'support@kts10pipsbots.com', 'type' => 'email', 'description' => 'Support email', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'description' => 'Enable/disable maintenance mode', 'group' => 'system'],
            ['key' => 'groq_api_key', 'value' => env('GROQ_API_KEY', ''), 'type' => 'password', 'description' => 'Groq API key', 'group' => 'api_keys'],
            ['key' => 'openai_api_key', 'value' => '', 'type' => 'password', 'description' => 'OpenAI API key', 'group' => 'api_keys'],
            ['key' => 'firebase_key', 'value' => '', 'type' => 'password', 'description' => 'Firebase credentials', 'group' => 'api_keys'],
            ['key' => 'jazzcash_merchant_id', 'value' => '', 'type' => 'text', 'description' => 'JazzCash merchant ID', 'group' => 'payment'],
            ['key' => 'jazzcash_password', 'value' => '', 'type' => 'password', 'description' => 'JazzCash password', 'group' => 'payment'],
            ['key' => 'easypaisa_store_id', 'value' => '', 'type' => 'text', 'description' => 'EasyPaisa store ID', 'group' => 'payment'],
            ['key' => 'easypaisa_password', 'value' => '', 'type' => 'password', 'description' => 'EasyPaisa password', 'group' => 'payment'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
