<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cliente extends Model
{
    protected $fillable = ['telefono', 'nombre', 'direccion', 'email', 'whatsapp_id'];

    public function pedidos(): HasMany { return $this->hasMany(Pedido::class); }
    public function conversaciones(): HasMany { return $this->hasMany(Conversacion::class); }
    public function carrito(): HasOne { return $this->hasOne(Carrito::class); }
}
