<?php

namespace App\Providers;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $identity = new SystemSetting(['name'=>'ChatBoalar','tagline'=>'WHATSAPP + IA','dashboard_title'=>'Panel del chatbot','dashboard_subtitle'=>'Productos de limpieza · WhatsApp Cloud API · OpenAI','primary_color'=>'#18a66a','sidebar_color'=>'#10271f']);
        View::share('identidad', $identity);
        try {
            if (Schema::hasTable('system_settings')) { $identity = SystemSetting::firstOrCreate([]); }
            View::share('identidad', $identity);
            if (! Schema::hasTable('integration_settings')) { return; }
            $integrations = IntegrationSetting::where('enabled', true)->get()->keyBy('provider');
            if ($whatsapp = $integrations->get('whatsapp')) {
                config(['services.whatsapp.access_token' => $whatsapp->access_token, 'services.whatsapp.app_secret' => $whatsapp->secret,
                    'services.whatsapp.phone_number_id' => data_get($whatsapp->settings, 'phone_number_id'), 'services.whatsapp.verify_token' => data_get($whatsapp->settings, 'verify_token'),
                    'services.whatsapp.api_version' => data_get($whatsapp->settings, 'api_version', 'v23.0')]);
            }
            if ($openai = $integrations->get('openai')) {
                config(['services.openai.api_key' => $openai->access_token, 'services.openai.model' => data_get($openai->settings, 'model', 'gpt-5.6-sol'),
                    'services.openai.max_tokens' => data_get($openai->settings, 'max_tokens', 500), 'services.openai.temperature' => data_get($openai->settings, 'temperature', .7)]);
            }
            if ($evolution = $integrations->get('evolution')) {
                config(['services.evolution.enabled' => true, 'services.evolution.base_url' => rtrim((string) data_get($evolution->settings, 'base_url'), '/'),
                    'services.evolution.api_key' => $evolution->access_token, 'services.evolution.instance' => data_get($evolution->settings, 'instance'),
                    'services.evolution.webhook_secret' => $evolution->secret]);
            }
        } catch (\Throwable) {
            // La aplicación puede arrancar aunque MySQL aún no esté disponible.
        }
    }
}
