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
        Schema::table('personas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_depto_residencia')->nullable()->after('direccion_domicilio');
            $table->foreign('id_depto_residencia')->references('id_departamento')->on('departamentos');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropForeign(['id_depto_residencia']);
            $table->dropColumn('id_depto_residencia');
        });
    }
};
