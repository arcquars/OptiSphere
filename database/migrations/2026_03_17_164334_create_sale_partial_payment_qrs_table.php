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
        Schema::create('sale_partial_payment_qrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('pago_qr_id')->constrained('pago_qrs')->onDelete('cascade');
            
            // Campo extra para el estado del pago parcial
            $table->string('status')->default('PENDING'); 
            $table->decimal('amount', 10, 2)->nullable();
            
            // Aconsejo agregar timestamps para auditoría
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_partial_payment_qrs');
    }
};
