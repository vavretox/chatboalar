<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::firstOrCreate([], [
            'name' => 'ChatBoalar',
            'tagline' => 'WHATSAPP + IA',
            'dashboard_title' => 'Panel del chatbot',
            'dashboard_subtitle' => 'Productos de limpieza · Evolution API · OpenAI',
            'primary_color' => '#520f0a',
            'sidebar_color' => '#2b2726',
        ]);
    }
}
