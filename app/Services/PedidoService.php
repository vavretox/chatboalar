<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Cliente;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class PedidoService
{
    public function agregar(Cliente $cliente, Producto $producto, int $cantidad): Carrito
    {
        if ($cantidad < 1 || ! $producto->tieneStock($cantidad)) {
            throw new \DomainException("Solo hay {$producto->stock} unidades disponibles de {$producto->nombre}.");
        }

        $carrito = Carrito::firstOrNew(['cliente_id' => $cliente->id]);
        $items = collect($carrito->items ?? []);
        $existente = $items->firstWhere('producto_id', $producto->id);
        $nuevaCantidad = $cantidad + (int) ($existente['cantidad'] ?? 0);
        if (! $producto->tieneStock($nuevaCantidad)) {
            throw new \DomainException("Tu carrito superaría el stock disponible de {$producto->nombre}.");
        }

        $items = $items->reject(fn ($item) => (int) $item['producto_id'] === $producto->id);
        $items->push(['producto_id' => $producto->id, 'codigo' => $producto->codigo, 'nombre' => $producto->nombre, 'cantidad' => $nuevaCantidad, 'precio' => (float) $producto->precio]);
        $carrito->fill(['items' => $items->values()->all(), 'ultima_actividad' => now()])->save();

        return $carrito->refresh();
    }

    public function resumen(Cliente $cliente): string
    {
        $items = collect($cliente->carrito?->items ?? []);
        if ($items->isEmpty()) {
            return '🛒 Tu carrito está vacío. Escribe “catálogo” para ver los productos.';
        }
        $total = $items->sum(fn ($item) => $item['cantidad'] * $item['precio']);
        return "🛒 Tu carrito:\n".$items->map(fn ($item) => "• {$item['cantidad']} × {$item['nombre']} — $".number_format($item['cantidad'] * $item['precio'], 2, ',', '.'))->implode("\n")
            ."\nTotal: $".number_format($total, 2, ',', '.')."\n\nEscribe “confirmar pedido” para finalizar.";
    }

    public function confirmar(Cliente $cliente): Pedido
    {
        return DB::transaction(function () use ($cliente): Pedido {
            $carrito = Carrito::where('cliente_id', $cliente->id)->lockForUpdate()->first();
            $items = collect($carrito?->items ?? []);
            if ($items->isEmpty()) { throw new \DomainException('Tu carrito está vacío.'); }

            $productos = Producto::whereIn('id', $items->pluck('producto_id'))->lockForUpdate()->get()->keyBy('id');
            $subtotal = $items->sum(fn ($item) => $item['cantidad'] * $item['precio']);
            $pedido = Pedido::create(['cliente_id' => $cliente->id, 'subtotal' => $subtotal, 'iva' => 0, 'total' => $subtotal, 'estado' => 'confirmado', 'direccion_entrega' => $cliente->direccion]);

            foreach ($items as $item) {
                $producto = $productos->get($item['producto_id']);
                if (! $producto || ! $producto->tieneStock($item['cantidad'])) { throw new \DomainException("Stock insuficiente para {$item['nombre']}."); }
                $producto->descontarStock($item['cantidad']);
                $pedido->detalles()->create(['producto_id' => $producto->id, 'cantidad' => $item['cantidad'], 'precio_unitario' => $item['precio'], 'subtotal' => $item['cantidad'] * $item['precio']]);
            }
            $carrito->delete();
            return $pedido;
        });
    }

    public function ultimoPedido(Cliente $cliente): ?Pedido { return $cliente->pedidos()->latest()->first(); }

    public function cancelar(Cliente $cliente): Pedido
    {
        return DB::transaction(function () use ($cliente): Pedido {
            $pedido = $cliente->pedidos()->with('detalles.producto')->latest()->lockForUpdate()->firstOrFail();
            if (! $pedido->esCancelable()) { throw new \DomainException("El pedido {$pedido->numero_pedido} ya no puede cancelarse."); }
            foreach ($pedido->detalles as $detalle) { $detalle->producto?->increment('stock', $detalle->cantidad); }
            $pedido->update(['estado' => 'cancelado']);
            return $pedido;
        });
    }
}
