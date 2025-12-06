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
        Schema::create('siat_sucursales_puntos_ventas', function (Blueprint $table) {
            $table->id();
            $table->integer('sucursal')->default(0);
            $table->integer('punto_venta')->default(0);
            $table->string(column: 'cuis')->nullable();
            $table->dateTime('cuis_date')->nullable();
            $table->boolean('active')->default(true);

            $table->softDeletes();

            $table->bigInteger('siat_property_id')->unsigned()->index()->nullable();
            $table->foreign('siat_property_id')
                ->references('id')
                ->on('siat_properties')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siat_sucursales_puntos_ventas');
    }
};
