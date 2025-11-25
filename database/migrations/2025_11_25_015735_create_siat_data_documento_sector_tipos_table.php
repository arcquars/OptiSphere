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
        Schema::create('siat_datas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_catalogo', 50)->index(); 
 
            $table->string('codigo_clasificador', 25);
            $table->string('descripcion', 255);
            $table->timestamps();

        });

        Schema::table('siat_datas', function (Blueprint $table) {
            $table->unsignedBigInteger('siat_spv_id');
            $table->foreign('siat_spv_id')->references('id')->on('siat_sucursales_puntos_ventas');

            $table->unique(['tipo_catalogo', 'codigo_clasificador', 'siat_spv_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siat_datas');
    }
};
