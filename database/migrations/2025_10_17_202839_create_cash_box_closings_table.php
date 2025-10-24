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
        Schema::create('cash_box_closings', function (Blueprint $table) {
            $table->id();

            // Relaciones
            // Asumo que tienes tablas 'branches' y 'users'
            $table->foreignId('branch_id')->constrained()->comment('Sucursal donde se realiza el cierre.');
            $table->foreignId('user_id')->constrained()->comment('Usuario que realiza el cierre (cajero).');

            // Tiempos
            $table->dateTime('opening_time')->comment('Fecha y hora en que inició el periodo de caja.');
            $table->dateTime('closing_time')->nullable()->comment('Fecha y hora en que se cerró la caja.');

            // Saldos (todos usando precision(10, 2) para el manejo de dinero)
            $table->decimal('initial_balance', 10, 2)->comment('Saldo con el que se abrió la caja (fondo de arranque).');
            $table->decimal('expected_balance', 10, 2)->comment('Saldo total esperado según el sistema (ventas + cobros + movs).');
            $table->decimal('actual_balance', 10, 2)->comment('Saldo físico contado por el usuario al cerrar.');
            $table->decimal('difference', 10, 2)->default(0.00)->comment('Diferencia entre el saldo esperado y el físico.');

            // Notas o comentarios sobre el cierre (ej: justificación de diferencias)
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_box_closings');
    }
};
