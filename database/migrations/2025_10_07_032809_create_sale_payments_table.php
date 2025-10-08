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
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');

            // DATOS DEL ABONO
            $table->decimal('amount', 10, 2)->comment('Monto de este abono específico.');
            $table->string('payment_method')->comment('Método de pago usado (Efectivo, Tarjeta, Transferencia, etc.).');
            $table->string('reference')->nullable()->comment('Referencia o número de voucher del pago, si aplica.');

            // AUDITORÍA
            // Usuario que registró el abono en el sistema
            $table->foreignId('user_id')->constrained('users');
            // Sucursal donde se recibió el abono (importante para cierres de caja)
            $table->foreignId('branch_id')->constrained('branches');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
    }
};
