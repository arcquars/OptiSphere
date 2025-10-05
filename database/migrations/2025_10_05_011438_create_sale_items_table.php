<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');

            // Elemento vendido (Producto o Servicio)
            $table->morphs('salable');

            $table->decimal('quantity', 10, 2);
            $table->decimal('base_price', 10, 2)->comment('Precio original sin descuentos');

            // Detalle de Promoción
            $table->foreignId('promotion_id')->nullable()->constrained('promotions');
            $table->decimal('promotion_discount_rate', 5, 2)->default(0)->comment('Porcentaje de descuento aplicado');

            // Precios finales
            $table->decimal('final_price_per_unit', 10, 2)->comment('Precio unitario después de promoción');
            $table->decimal('subtotal', 10, 2)->comment('Cantidad * Precio Final por Unidad');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
