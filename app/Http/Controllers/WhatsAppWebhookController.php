<?php
namespace App\Http\Controllers;
use App\Helpers\WhatsAppHelper;
use App\Jobs\ProcessWhatsAppMessage;
use App\Models\Cliente;
use App\Models\Conversacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
class WhatsAppWebhookController extends Controller {
    public function verify(Request $request): Response {
        $valid=$request->query('hub_mode')==='subscribe'&&hash_equals((string)config('services.whatsapp.verify_token'),(string)$request->query('hub_verify_token'));
        return $valid?response((string)$request->query('hub_challenge'),200):response('Verification failed',403);
    }
    public function webhook(Request $request): JsonResponse {
        $encolados = 0;
        foreach (data_get($request->all(), 'entry.0.changes.0.value.messages', []) as $message) {
            $texto = WhatsAppHelper::mensajeTexto($message);
            if (! is_string($texto) || blank($texto)) { continue; }
            $telefono = WhatsAppHelper::normalizarTelefono((string) ($message['from'] ?? ''));
            if ($telefono === '') { continue; }
            $cliente = Cliente::firstOrCreate(['telefono' => $telefono], ['nombre' => "Cliente {$telefono}", 'whatsapp_id' => $telefono]);
            Conversacion::create(['cliente_id' => $cliente->id, 'tipo' => 'entrante', 'mensaje' => $texto, 'metadata' => ['whatsapp_message_id' => $message['id'] ?? null]]);
            ProcessWhatsAppMessage::dispatch($cliente->id, $texto)->onQueue('whatsapp');
            $encolados++;
        }
        return response()->json(['status' => 'accepted', 'queued' => $encolados]);
    }
}
