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
        Schema::create('optical_properties', function (Blueprint $table) {
            $table->id();

            $table->double('sphere', 10, 2);
            $table->double('cylinder', 10, 2);
            $table->integer('axis');
            $table->integer('add');

            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('optical_properties');
    }
};
