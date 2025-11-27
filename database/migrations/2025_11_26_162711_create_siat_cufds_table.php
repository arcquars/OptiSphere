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
        Schema::create('siat_cufds', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 255);
            $table->string('codigo_control', 255);
            $table->string('direccion', 255);
            $table->dateTime('fecha_vigencia');

            $table->timestamps();
        });

        Schema::table('siat_cufds', function (Blueprint $table) {
            $table->unsignedBigInteger('siat_spv_id');
            $table->foreign('siat_spv_id')->references('id')->on('siat_sucursales_puntos_ventas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siat_cufds');
    }
};
