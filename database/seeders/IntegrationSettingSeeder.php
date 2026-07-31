<?php

namespace Database\Seeders;

use App\Models\IntegrationSetting;
use Illuminate\Database\Seeder;

class IntegrationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $key = env('EVOLUTION_API_KEY');
        IntegrationSetting::firstOrCreate(['provider' => 'evolution'], [
            'access_token' => filled($key) ? $key : null,
            'settings' => [
                'base_url' => rtrim((string) env('EVOLUTION_API_URL', ''), '/'),
                'instance' => env('EVOLUTION_INSTANCE', 'CHATBOT'),
            ],
            'enabled' => filled($key) && filter_var(env('EVOLUTION_ENABLED', false), FILTER_VALIDATE_BOOL),
        ]);

        IntegrationSetting::firstOrCreate(['provider' => 'openai'], [
            'settings' => ['model' => env('OPENAI_MODEL', 'gpt-5.6-sol'), 'max_tokens' => 500, 'temperature' => 0.7],
            'enabled' => false,
        ]);
    }
}
