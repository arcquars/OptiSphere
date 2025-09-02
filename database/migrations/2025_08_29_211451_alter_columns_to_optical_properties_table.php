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
        Schema::table('optical_properties', function (Blueprint $table) {
            $table->dropColumn('axis');
            $table->dropColumn('add');

            $table->string('base_code');
            $table->enum('type', ['+', '-'])->default('+');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('optical_properties', function (Blueprint $table) {
            $table->integer('axis');
            $table->integer('add');

            $table->dropColumn('base_code');
            $table->dropColumn('type');
        });
    }
};
