<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Renombrar la tabla de product_prices a prices
        Schema::rename('product_prices', 'prices');

        // 2. Añadir las columnas polimórficas
        Schema::table('prices', function (Blueprint $table) {
            $table->string('priceable_type')->after('product_id');
            $table->unsignedBigInteger('priceable_id')->after('product_id');

            // Añadir un índice para mejorar el rendimiento de las consultas polimórficas
            $table->index(['priceable_id', 'priceable_type']);
        });

        // 3. Rellenar los nuevos campos con los datos existentes de productos
        DB::table('prices')->update([
            'priceable_type' => \App\Models\Product::class,
            'priceable_id' => DB::raw('product_id')
        ]);

        // 4. Eliminar la columna original de product_id
        Schema::table('prices', function (Blueprint $table) {
            // Buscar el nombre del foreing key en la base de datos, aqui ocurre un error
//            $table->dropForeign("product_prices_product_id_foreign");
//            $table->dropColumn('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para revertir, hacemos los pasos inversos
        Schema::table('prices', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->after('priceable_type');
        });

        DB::table('prices')
            ->where('priceable_type', \App\Models\Product::class)
            ->update(['product_id' => DB::raw('priceable_id')]);

        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn(['priceable_id', 'priceable_type']);
        });

        Schema::rename('prices', 'product_prices');

        Schema::table('product_prices', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
        });
    }
};
