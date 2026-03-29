<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sexo', function (Blueprint $table) {
            $table->id('id_sexo');
            $table->string('sexo');
            $table->timestamps();
        });

        Schema::create('grado_academico', function (Blueprint $table) {
            $table->id('id_grado_academico');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('personas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('primer_apellido');
            $table->string('segundo_apellido')->nullable();
            $table->string('nombres');
            $table->string('ci', 50)->unique();
            $table->string('complemento', 5)->nullable();
            $table->foreignId('id_ci_expedido')->constrained('departamentos', 'id_departamento');
            $table->foreignId('id_sexo')->constrained('sexo', 'id_sexo');
            $table->date('fecha_nacimiento')->nullable();
            $table->string('celular_personal', 15);
            $table->string('correo_personal')->unique();
            $table->string('estado_civil');
            $table->foreignId('id_nacionalidad')->constrained('nacionalidades', 'id_nacionalidad');
            $table->string('direccion_domicilio');
            $table->foreignId('id_ciudad')->constrained('ciudades', 'id_ciudad');
            $table->foreignId('id_pais')->constrained('paises', 'id_pais');
            $table->string('foto')->nullable();
            $table->string('estado_onboarding')->default('sin_iniciar');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
        Schema::dropIfExists('grado_academico');
        Schema::dropIfExists('sexo');
    }
};
