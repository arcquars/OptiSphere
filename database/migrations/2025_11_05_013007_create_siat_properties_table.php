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
        Schema::create('siat_properties', function (Blueprint $table) {
            $table->id();

            $table->string('system_name', 250);
            $table->string('system_code', 250);
            $table->string('nit', 30);
            $table->string('company_name', 250);
            $table->integer('modality');
            $table->integer('environment');
            $table->string('city', 250);
            $table->string('phone', 50)->nullable();
            $table->text('token');
            $table->string('print_size', 100);
            $table->string('path_logo', 250)->nullable();
            $table->string('path_digital_signature', 250)->nullable();

            $table->boolean('is_actived')->default(true);
            $table->boolean('is_validated')->default(false);

            $table->timestamps();
        });

        Schema::table('siat_properties', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id');
        
            $table->foreign('branch_id')->references('id')->on('branches');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siat_properties');
    }
};
