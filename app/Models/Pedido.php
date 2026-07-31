<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pedido extends Model
{
    protected $fillable = ['cliente_id', 'numero_pedido', 'subtotal', 'iva', 'total', 'estado', 'notas', 'direccion_entrega', 'fecha_entrega'];
    protected function casts(): array { return ['subtotal' => 'decimal:2', 'iva' => 'decimal:2', 'total' => 'decimal:2', 'fecha_entrega' => 'datetime']; }
    protected static function booted(): void { static::creating(fn (Pedido $pedido) => $pedido->numero_pedido ??= 'PED-'.now()->format('Ymd').'-'.Str::upper(Str::random(6))); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function detalles(): HasMany { return $this->hasMany(PedidoDetalle::class); }
    public function esCancelable(): bool { return in_array($this->estado, ['pendiente', 'confirmado'], true); }
}
