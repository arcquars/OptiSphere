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
        Schema::table('services', function (Blueprint $table) {
            $table->string('siat_data_medida_code', 25)->nullable()->after('is_active');
            $table->string('siat_data_actividad_code', 25)->nullable()->after('siat_data_medida_code');
            $table->string('siat_data_product_code', 25)->nullable()->after('siat_data_actividad_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('siat_data_medida_code');
            $table->dropColumn('siat_data_actividad_code');
            $table->dropColumn('siat_data_product_code');
        });
    }
};
