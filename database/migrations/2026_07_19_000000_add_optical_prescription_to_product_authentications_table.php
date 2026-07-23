<?php

declare(strict_types=1);

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
        Schema::table('product_authentications', function (Blueprint $table): void {
            // Receta óptica (Lejos). Nullable: hay productos sin receta (armazón, accesorio).
            $table->double('od_sphere', 10, 2)->nullable()->after('fecha_compra');
            $table->double('od_cylinder', 10, 2)->nullable()->after('od_sphere');
            $table->integer('od_axis')->nullable()->after('od_cylinder');
            $table->double('oi_sphere', 10, 2)->nullable()->after('od_axis');
            $table->double('oi_cylinder', 10, 2)->nullable()->after('oi_sphere');
            $table->integer('oi_axis')->nullable()->after('oi_cylinder');
            $table->double('add', 10, 2)->nullable()->after('oi_axis');
            $table->double('dip', 10, 2)->nullable()->after('add');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_authentications', function (Blueprint $table): void {
            $table->dropColumn([
                'od_sphere',
                'od_cylinder',
                'od_axis',
                'oi_sphere',
                'oi_cylinder',
                'oi_axis',
                'add',
                'dip',
            ]);
        });
    }
};
