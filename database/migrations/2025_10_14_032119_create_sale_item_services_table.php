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
        Schema::create('sale_item_services', function (Blueprint $table) {
            $table->id();

            // Relación con el SaleItem padre (el producto).
            $table->foreignId('sale_item_id')
                ->constrained('sale_items')
                ->onDelete('cascade');

            // Relación con el servicio específico vendido.
            $table->foreignId('service_id')
                ->constrained('services')
                ->onDelete('restrict');

            $table->foreignId('promotion_id')->nullable()->constrained('promotions');
            $table->decimal('promotion_discount_rate', 5, 2)->default(0)->comment('Porcentaje de descuento aplicado');

            $table->float('quantity');
            $table->float('price_per_unit');
            $table->float('subtotal');

            $table->timestamps();

            // Esto asegura que un servicio solo se adjunte una vez por item de venta, si es necesario.
            // $table->unique(['sale_item_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_item_services');
    }
};
