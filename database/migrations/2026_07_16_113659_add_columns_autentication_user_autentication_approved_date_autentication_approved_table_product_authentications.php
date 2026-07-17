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
        Schema::table('product_authentications', function (Blueprint $table) {
            $table->boolean('is_authentication')->default(false)->after('product_id');
            $table->dateTime('authentication_approved_date')->nullable()->after('is_authentication');
            $table->string('authentication_approved_by')->nullable()->after('authentication_approved_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_authentications', function (Blueprint $table) {
            $table->dropColumn('is_authentication');
            $table->dropColumn('authentication_approved_date');
            $table->dropColumn('authentication_approved_by');
        });
    }
};
