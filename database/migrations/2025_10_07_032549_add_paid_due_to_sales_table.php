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
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('paid_amount', 10, 2)->default(0.00)->after('total_amount')->comment('Monto total abonado a esta venta.');
            $table->decimal('due_amount', 10, 2)->after('paid_amount')->comment('Saldo pendiente (Total - PaidAmount).');

            $table->string('status')->default('COMPLETADA')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'due_amount']);
        });
    }
};
