<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingredientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->decimal('umbral', 12, 3)->default(0);
            $table->timestamps();
        });

        Schema::create('stock_sucursal_ingrediente', function (Blueprint $table) {
            $table->id();
            $table->string('sucursal', 120);
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->decimal('cantidad', 14, 3)->default(0);
            $table->timestamps();
            $table->unique(['sucursal', 'ingrediente_id']);
        });

        Schema::create('producto_ingrediente', function (Blueprint $table) {
            $table->id();
            $table->string('producto_nombre', 120);
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->decimal('cantidad_por_unidad', 14, 3);
            $table->timestamps();
            $table->index('producto_nombre');
        });

        Schema::create('inventario_movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('sucursal', 120);
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->decimal('cantidad_delta', 14, 3);
            $table->string('tipo', 40);
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('alertas_stock', function (Blueprint $table) {
            $table->id();
            $table->string('sucursal', 120);
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->string('nivel', 20);
            $table->string('mensaje', 500);
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('sucursal', 120);
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->decimal('cantidad_sugerida', 14, 3);
            $table->string('estado', 30)->default('pendiente');
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('menu_items_bloqueados', function (Blueprint $table) {
            $table->id();
            $table->string('sucursal', 120);
            $table->string('producto_nombre', 120);
            $table->foreignId('ingrediente_id')->constrained('ingredientes')->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos')->nullOnDelete();
            $table->timestamps();
            $table->unique(['sucursal', 'producto_nombre'], 'menu_bloq_suc_prod_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items_bloqueados');
        Schema::dropIfExists('ordenes_compra');
        Schema::dropIfExists('alertas_stock');
        Schema::dropIfExists('inventario_movimientos');
        Schema::dropIfExists('producto_ingrediente');
        Schema::dropIfExists('stock_sucursal_ingrediente');
        Schema::dropIfExists('ingredientes');
    }
};
