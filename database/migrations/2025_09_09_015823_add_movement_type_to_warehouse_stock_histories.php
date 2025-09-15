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
        Schema::table('warehouse_stock_histories', function (Blueprint $table) {
            $table->string('movement_type', 200)->nullable();
            $table->bigInteger('type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_stock_histories', function (Blueprint $table) {
            $table->dropColumn('movement_type');
            $table->dropColumn('type_id');
        });
    }
};
