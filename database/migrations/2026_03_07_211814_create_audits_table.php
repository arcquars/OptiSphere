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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->morphs('auditable'); // Crea auditable_type y auditable_id
            $table->json('old_values')->nullable(); // Estado anterior
            $table->json('new_values')->nullable(); // Estado nuevo
            $table->foreignId('user_id')->nullable()->constrained(); // Quién hizo el cambio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
