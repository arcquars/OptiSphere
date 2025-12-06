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
        Schema::create('amyr_connection_branches', function (Blueprint $table) {
            $table->id();

            $table->string(column: 'amyr_user');
            $table->string(column: 'amyr_password');
            $table->integer('sucursal')->default(0);
            $table->integer('point_sale')->default(0);

            $table->boolean('is_actived')->default(false);

            $table->text('token')->nullable();

            $table->timestamps();
        });

        Schema::table('amyr_connection_branches', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amyr_connection_branches');
    }
};
