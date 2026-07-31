<?php

namespace App\Http\Controllers;

use App\Models\IntegrationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        $integraciones = IntegrationSetting::all()->keyBy('provider');
        return view('integraciones.index', [
            'whatsapp' => $integraciones->get('whatsapp', new IntegrationSetting(['provider' => 'whatsapp'])),
            'openai' => $integraciones->get('openai', new IntegrationSetting(['provider' => 'openai'])),
            'evolution' => $integraciones->get('evolution', new IntegrationSetting(['provider' => 'evolution'])),
            'mysql' => $this->mysqlStatus(),
            'canalPrincipal' => $integraciones->first(fn (IntegrationSetting $item) => in_array($item->provider, ['whatsapp', 'evolution'], true) && $item->enabled)?->provider ?? 'ninguno',
        ]);
    }

    public function channel(Request $request): RedirectResponse
    {
        $data = $request->validate(['channel' => ['required', 'in:evolution,whatsapp,ninguno']]);

        DB::transaction(function () use ($data): void {
            IntegrationSetting::whereIn('provider', ['whatsapp', 'evolution'])->update(['enabled' => false]);

            if ($data['channel'] !== 'ninguno') {
                $integration = IntegrationSetting::where('provider', $data['channel'])->first();
                if (! $integration || blank($integration->access_token)) {
                    throw ValidationException::withMessages(['channel' => 'Primero guarda las credenciales del canal seleccionado.']);
                }
                $integration->update(['enabled' => true]);
            }
        });

        $nombre = $data['channel'] === 'evolution' ? 'Evolution API' : ($data['channel'] === 'whatsapp' ? 'WhatsApp Cloud API' : 'ningún canal');
        return back()->with('success', "Canal principal actualizado: {$nombre}.");
    }

    public function update(Request $request, string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['whatsapp', 'openai', 'evolution'], true), 404);
        $integration = IntegrationSetting::firstOrNew(['provider' => $provider]);

        if ($provider === 'whatsapp') {
            $data = $request->validate([
                'access_token' => ['nullable', 'string', 'max:8000'], 'app_secret' => ['nullable', 'string', 'max:1000'],
                'phone_number_id' => ['required', 'string', 'max:100'], 'verify_token' => ['required', 'string', 'max:255'],
                'api_version' => ['required', 'regex:/^v\d+\.\d+$/'], 'enabled' => ['nullable', 'boolean'],
            ]);
            $settings = ['phone_number_id' => $data['phone_number_id'], 'verify_token' => $data['verify_token'], 'api_version' => $data['api_version']];
            if (filled($data['app_secret'] ?? null)) { $integration->secret = $data['app_secret']; }
        } elseif ($provider === 'openai') {
            $data = $request->validate([
                'access_token' => ['nullable', 'string', 'max:8000'], 'model' => ['required', 'string', 'max:150'],
                'max_tokens' => ['required', 'integer', 'min:50', 'max:16000'], 'temperature' => ['required', 'numeric', 'min:0', 'max:2'],
                'enabled' => ['nullable', 'boolean'],
            ]);
            $settings = ['model' => $data['model'], 'max_tokens' => (int) $data['max_tokens'], 'temperature' => (float) $data['temperature']];
        } else {
            $data = $request->validate(['access_token' => ['nullable','string','max:8000'],'webhook_secret' => ['nullable','string','max:1000'],
                'base_url' => ['required','url','max:2048'],'instance' => ['required','string','max:150'],'enabled' => ['nullable','boolean']]);
            $settings = ['base_url' => rtrim($data['base_url'],'/'), 'instance' => $data['instance']];
            if (filled($data['webhook_secret'] ?? null)) { $integration->secret = $data['webhook_secret']; }
        }

        if (filled($data['access_token'] ?? null)) { $integration->access_token = $data['access_token']; }
        $enabled = in_array($provider, ['whatsapp', 'evolution'], true) ? (bool) $integration->enabled : $request->boolean('enabled');
        $integration->fill(['settings' => $settings, 'enabled' => $enabled])->save();
        return back()->with('success', ucfirst($provider).' guardado correctamente.');
    }

    public function test(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, ['whatsapp', 'openai', 'evolution', 'mysql'], true), 404);
        if ($provider === 'mysql') {
            $status = $this->mysqlStatus();
            return back()->with($status['success'] ? 'success' : 'error', $status['message']);
        }

        $integration = IntegrationSetting::where('provider', $provider)->firstOrFail();
        try {
            if (! filled($integration->access_token)) { throw new \RuntimeException('Primero debes guardar una clave de acceso.'); }
            if ($provider === 'whatsapp') {
                $phoneId = data_get($integration->settings, 'phone_number_id');
                $version = data_get($integration->settings, 'api_version', 'v23.0');
                $response = Http::withToken($integration->access_token)->timeout(15)->get("https://graph.facebook.com/{$version}/{$phoneId}", ['fields' => 'display_phone_number,verified_name']);
                $response->throw();
                $message = 'Conexión correcta con WhatsApp: '.($response->json('display_phone_number') ?: $phoneId).'.';
            } elseif ($provider === 'openai') {
                Http::withToken($integration->access_token)->timeout(15)->get('https://api.openai.com/v1/models')->throw();
                $message = 'Conexión correcta con OpenAI.';
            } else {
                $base = rtrim((string) data_get($integration->settings,'base_url'),'/'); $instance = data_get($integration->settings,'instance');
                $response = Http::withHeaders(['apikey'=>$integration->access_token])->timeout(15)->get("{$base}/instance/connectionState/{$instance}")->throw();
                $state = data_get($response->json(),'instance.state','desconocido'); $message = "Evolution API respondió. Estado de la instancia: {$state}.";
            }
            $success = true;
        } catch (\Throwable $e) { $success = false; $message = 'No se pudo conectar: '.$e->getMessage(); }

        $integration->update(['last_tested_at' => now(), 'last_test_success' => $success, 'last_test_message' => mb_substr($message, 0, 1000)]);
        return back()->with($success ? 'success' : 'error', $message);
    }

    private function mysqlStatus(): array
    {
        try {
            DB::select('SELECT 1');
            return ['success' => true, 'message' => 'MySQL está conectado correctamente.', 'database' => DB::connection()->getDatabaseName(), 'host' => config('database.connections.mysql.host')];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'MySQL no responde: '.$e->getMessage(), 'database' => null, 'host' => config('database.connections.mysql.host')];
        }
    }
}
