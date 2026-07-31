<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $fillable = ['codigo', 'nombre', 'descripcion', 'precio', 'stock', 'categoria', 'unidad_medida', 'imagen_url', 'document_path', 'document_name', 'activo'];
    protected function casts(): array { return ['precio' => 'decimal:2', 'activo' => 'boolean']; }
    public function pedidoDetalles(): HasMany { return $this->hasMany(PedidoDetalle::class); }
    public function scopeActivos(Builder $query): Builder { return $query->where('activo', true); }
    public function scopeDisponibles(Builder $query): Builder { return $query->where('stock', '>', 0); }
    public function tieneStock(int $cantidad): bool { return $this->stock >= $cantidad; }
    public function descontarStock(int $cantidad): void
    {
        if (! $this->tieneStock($cantidad)) { throw new \DomainException('Stock insuficiente.'); }
        $this->decrement('stock', $cantidad);
    }
}
