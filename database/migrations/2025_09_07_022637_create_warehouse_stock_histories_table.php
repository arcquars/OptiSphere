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
        Schema::create('warehouse_stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_stock_id')->constrained('warehouse_stocks');
            $table->integer('old_quantity')->default(0);
            $table->integer('new_quantity')->default(0);
            $table->integer('difference'); // Puede ser positivo o negativo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_histories');
    }
};
