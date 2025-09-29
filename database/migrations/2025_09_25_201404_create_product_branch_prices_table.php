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
        Schema::create('product_branch_prices', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['normal', 'especial', 'mayorista'])->default('normal');
            $table->double('price', 10, 2)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_branch_prices');
    }
};
