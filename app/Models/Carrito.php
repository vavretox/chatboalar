<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carrito extends Model
{
    protected $fillable = ['cliente_id', 'items', 'ultima_actividad'];
    protected function casts(): array { return ['items' => 'array', 'ultima_actividad' => 'datetime']; }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
}
