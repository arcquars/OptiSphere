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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            // Relaciones
            $table->foreignId('branch_id')->constrained('branches'); // Sucursal donde se hizo la venta
            $table->foreignId('customer_id')->constrained('customers'); // Cliente
            $table->foreignId('user_id')->constrained('users'); // Vendedor

            // Totales
            $table->decimal('total_amount', 10, 2)->comment('Suma de sale_items.subtotal antes de final_discount');
            $table->decimal('final_discount', 10, 2)->default(0)->comment('Descuento total aplicado a toda la venta');
            $table->decimal('final_total', 10, 2)->comment('Monto final a pagar');

            // Otros datos
            $table->string('status', 50)->default('paid'); // pagada, pendiente, cancelada
            $table->string('payment_method', 50); // efectivo, tarjeta, credito

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
