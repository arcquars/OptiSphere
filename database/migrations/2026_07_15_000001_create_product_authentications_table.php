<?php

declare(strict_types=1);

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
        Schema::create('product_authentications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('products');
            // Datos del cliente que compró el producto
            $table->string('cliente');
            $table->date('fecha_compra');
            // Cliente frecuente (registro de customers) que registra la autenticación
            $table->foreignId('frequent_customer_id')->constrained('customers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_authentications');
    }
};
