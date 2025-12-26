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
        Schema::create('configuracion_bancos', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->text('password');
            $table->text('numero_cuenta');
            $table->string('api_key');
            $table->string('nombre_empresa');
            $table->string('codigo_empresa')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index('codigo_empresa');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_bancos');
    }
};
