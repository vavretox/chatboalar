<?php

namespace App\Services;

use App\Models\Cliente;

class ChatbotService
{
    public function __construct(private CatalogoService $catalogo, private PedidoService $pedidos, private AIService $ai) {}

    public function responder(Cliente $cliente, string $mensaje): string
    {
        $texto = mb_strtolower(trim($mensaje));
        try {
            if (preg_match('/\b(cat[aá]logo|productos|men[uú])\b/u', $texto)) { return $this->catalogo->textoCatalogo(); }
            if (preg_match('/\b(ver\s+)?carrito\b/u', $texto)) { return $this->pedidos->resumen($cliente); }
            if (preg_match('/\bconfirm(ar|o)?\s+(el\s+)?pedido\b/u', $texto)) {
                $pedido = $this->pedidos->confirmar($cliente);
                return "✅ Pedido {$pedido->numero_pedido} confirmado por $".number_format((float) $pedido->total, 2, ',', '.').'. Te avisaremos cuando cambie de estado.';
            }
            if (preg_match('/\b(estado|seguimiento|d[oó]nde).*pedido\b/u', $texto)) {
                $pedido = $this->pedidos->ultimoPedido($cliente);
                return $pedido ? "📦 Tu pedido {$pedido->numero_pedido} está: {$pedido->estado}." : 'Todavía no tienes pedidos registrados.';
            }
            if (preg_match('/\bcancel(ar|a|aci[oó]n).*pedido\b/u', $texto)) {
                $pedido = $this->pedidos->cancelar($cliente);
                return "Pedido {$pedido->numero_pedido} cancelado. El stock fue restituido.";
            }
            if (preg_match('/\b(?:quiero|agrega(?:r|me)?|a[nñ]ade(?:me)?|dame)\s+(\d+)\s+(.+)/u', $texto, $match)) {
                $producto = $this->catalogo->buscar($match[2]);
                if (! $producto) { return 'No encontré ese producto. Escribe “catálogo” para ver las opciones disponibles.'; }
                $this->pedidos->agregar($cliente, $producto, (int) $match[1]);
                return "✅ Agregué {$match[1]} × {$producto->nombre} a tu carrito.\n\n".$this->pedidos->resumen($cliente);
            }
        } catch (\DomainException $e) { return '⚠️ '.$e->getMessage(); }

        if (filled(config('services.openai.api_key'))) { return $this->ai->processMessage($cliente->id, $mensaje, $this->historial($cliente)); }
        return "¡Hola! Puedo mostrarte el catálogo, agregar productos al carrito, confirmar pedidos y consultar su estado. Escribe “catálogo” para comenzar.";
    }

    private function historial(Cliente $cliente): array
    {
        return $cliente->conversaciones()->latest()->limit(8)->get()->reverse()->map(fn ($item) => ['rol' => $item->tipo === 'entrante' ? 'user' : 'assistant', 'mensaje' => $item->mensaje])->values()->all();
    }
}
