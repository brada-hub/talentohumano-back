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
        // 1. Update sedes table to handle only the main 9 branches
        Schema::table('sedes', function (Blueprint $table) {
            $table->dropForeign(['id_sede_padre']);
            $table->dropColumn('id_sede_padre');
            $table->unsignedBigInteger('id_departamento')->nullable()->after('sigla');
        });

        // 2. Create campus table
        Schema::create('campus', function (Blueprint $table) {
            $table->id('id_campus');
            $table->string('nombre');
            $table->string('sigla');
            $table->unsignedBigInteger('id_sede');
            $table->string('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_sede')->references('id_sede')->on('sedes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus');
        Schema::table('sedes', function (Blueprint $table) {
            $table->dropColumn('id_departamento');
            $table->unsignedBigInteger('id_sede_padre')->nullable()->after('id_ciudad');
        });
    }
};
