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
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('branch_id')->constrained()->comment('Sucursal a la que pertenece el movimiento.');
            $table->foreignId('user_id')->constrained()->comment('Usuario que registra el movimiento.');

            // Relación opcional con el cierre (se llenará al momento del cierre)
            $table->foreignId('cash_box_closing_id')
                ->nullable()
                ->constrained()
                ->comment('Cierre de caja al que se imputa este movimiento.');

            // Datos del movimiento
            $table->enum('type', ['INCOME', 'EXPENSE'])->comment('Tipo de movimiento: INGRESO o EGRESO.');
            $table->decimal('amount', 10, 2)->comment('Monto del movimiento.');
            $table->string('description')->comment('Detalle o razón del movimiento (ej: pago de servicios, fondo extra).');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
