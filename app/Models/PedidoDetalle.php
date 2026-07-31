<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoDetalle extends Model
{
    protected $fillable = ['pedido_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal', 'descuento'];
    protected function casts(): array { return ['precio_unitario' => 'decimal:2', 'subtotal' => 'decimal:2', 'descuento' => 'decimal:2']; }
    public function pedido(): BelongsTo { return $this->belongsTo(Pedido::class); }
    public function producto(): BelongsTo { return $this->belongsTo(Producto::class); }
}
