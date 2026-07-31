<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table): void {
            $table->id();
            $table->string('telefono', 20)->unique();
            $table->string('nombre')->nullable();
            $table->text('direccion')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp_id')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('productos', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->string('categoria')->default('general');
            $table->string('unidad_medida')->default('unidad');
            $table->string('imagen_url')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['categoria', 'activo']);
        });

        Schema::create('pedidos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('numero_pedido')->unique();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'confirmado', 'preparando', 'enviado', 'entregado', 'cancelado'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->text('direccion_entrega')->nullable();
            $table->dateTime('fecha_entrega')->nullable();
            $table->timestamps();
            $table->index(['cliente_id', 'estado']);
        });

        Schema::create('pedido_detalles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('conversaciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->enum('tipo', ['entrante', 'saliente']);
            $table->text('mensaje');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['cliente_id', 'created_at']);
        });

        Schema::create('carritos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cliente_id')->unique()->constrained('clientes')->cascadeOnDelete();
            $table->json('items');
            $table->dateTime('ultima_actividad');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carritos');
        Schema::dropIfExists('conversaciones');
        Schema::dropIfExists('pedido_detalles');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('clientes');
    }
};
