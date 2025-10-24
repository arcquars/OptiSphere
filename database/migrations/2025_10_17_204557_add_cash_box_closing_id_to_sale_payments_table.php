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
        Schema::table('sale_payments', function (Blueprint $table) {
            $table->foreignId('cash_box_closing_id')
                ->nullable()
                ->after('notes') // Colócalo después de 'notes' o donde mejor te parezca
                ->constrained()
                ->comment('Cierre de caja al que pertenece esta venta.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_payments', function (Blueprint $table) {
            // Eliminar la clave foránea
            $table->dropForeign(['cash_box_closing_id']);
            // Eliminar la columna
            $table->dropColumn('cash_box_closing_id');
        });
    }
};
