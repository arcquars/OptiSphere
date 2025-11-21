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
        Schema::create('siat_data_actividad_documento_sectores', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_actividad', 25);
            $table->integer('codigo_documento_sector');
            $table->string('tipo_documento_sector',25);
            
            $table->timestamps();
        });

        Schema::table('siat_data_actividad_documento_sectores', function (Blueprint $table) {
            $table->unsignedBigInteger('siat_spv_id');
            $table->foreign('siat_spv_id')->references('id')->on('siat_sucursales_puntos_ventas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siat_data_actividad_documento_sectores');
    }
};
