<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversacion extends Model
{
    protected $table = 'conversaciones';

    protected $fillable = ['cliente_id', 'tipo', 'mensaje', 'metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
}
