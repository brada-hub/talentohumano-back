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
        // Module: Geo
        Schema::create('paises', function (Blueprint $table) {
            $table->id('id_pais');
            $table->string('nombre');
            $table->char('iso2', 2);
            $table->char('iso3', 3);
            $table->string('prefijo_telefonico');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('departamentos', function (Blueprint $table) {
            $table->id('id_departamento');
            $table->string('nombre');
            $table->char('codigo_expedido', 4); // "CB", "LP", etc. (sometimes 3-4 chars like "EXT")
            $table->foreignId('pais_id')->constrained('paises', 'id_pais');
            $table->timestamps();
        });

        Schema::create('ciudades', function (Blueprint $table) {
            $table->id('id_ciudad');
            $table->string('nombre');
            $table->foreignId('departamento_id')->constrained('departamentos', 'id_departamento');
            $table->timestamps();
        });

        Schema::create('nacionalidades', function (Blueprint $table) {
            $table->id('id_nacionalidad');
            $table->string('gentilicio');
            $table->foreignId('id_pais')->constrained('paises', 'id_pais');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nacionalidades');
        Schema::dropIfExists('ciudades');
        Schema::dropIfExists('departamentos');
        Schema::dropIfExists('paises');
    }
};
