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
        Schema::table('pago_qrs', function (Blueprint $table) {
            $table->string("sender_bank_code", 255)->nullable();
            $table->string("sender_name", 255)->nullable();
            $table->string("sender_document_id", 255)->nullable();
            $table->string("sender_account", 255)->nullable();
            $table->boolean("is_assigned")->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pago_qrs', function (Blueprint $table) {
            $table->dropColumn("sender_bank_code");
            $table->dropColumn("sender_name");
            $table->dropColumn("sender_document_id");
            $table->dropColumn("sender_account");
            $table->dropColumn("is_assigned");
        });
    }
};
