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
        Schema::create('pago_qrs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('qr_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 5);
            $table->string('description')->nullable();
            $table->string('branch_code', 10)->nullable();

            $table->tinyInteger('status')->default(0);
            $table->dateTime('payment_date')->nullable();

            $table->longText('qr_image')->nullable();
            $table->json('extra_data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_qrs');
    }
};
