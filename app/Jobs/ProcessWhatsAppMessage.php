<?php

namespace App\Jobs;

use App\Models\Cliente;
use App\Models\Conversacion;
use App\Services\ChatbotService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public array $backoff = [10, 30, 90];

    public function __construct(public int $clienteId, public string $mensaje) {}

    public function handle(ChatbotService $chatbot, WhatsAppService $whatsapp): void
    {
        $cliente = Cliente::findOrFail($this->clienteId);
        $respuesta = $chatbot->responder($cliente, $this->mensaje);
        Conversacion::create(['cliente_id' => $cliente->id, 'tipo' => 'saliente', 'mensaje' => $respuesta]);
        $whatsapp->sendTextMessage($cliente->telefono, $respuesta);
    }
}
