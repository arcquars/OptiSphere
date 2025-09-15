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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->onUpdate('cascade')->onDelete('cascade');
            $table->string('from_location_type', 200)->nullable();
            $table->integer('from_location_id')->nullable();
            $table->string('to_location_type', 200)->nullable();
            $table->integer('to_location_id')->nullable();

            $table->integer('old_quantity')->default(0);
            $table->integer('new_quantity')->default(0);
            $table->integer('difference'); // Puede ser positivo o negativo

            $table->string('type', 200);
            $table->text('description')->nullable();
            $table->bigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
