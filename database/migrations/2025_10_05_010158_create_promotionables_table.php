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
        Schema::create('promotionables', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            // Columnas polimórficas (Product o Service)
            $table->morphs('promotionable');
            // Clave compuesta para asegurar unicidad
            $table->unique(['promotion_id', 'promotionable_id', 'promotionable_type'], 'promotionable_unique');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotionables');
    }
};
