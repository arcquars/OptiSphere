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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->string('nit', 15)->unique()->nullable();
            $table->string('address', 250)->nullable();
            $table->string('email', 250)->nullable();
            $table->string('phone', 80)->nullable();

            $table->string('contact_info', 255);
            $table->boolean('can_buy_on_credit')->default(false);
            $table->enum('type', ['normal', 'especial', 'mayorista'])->default('normal');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
