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
        // 1. Formacion Pregrado
        Schema::create('formacion_pregrado', function (Blueprint $table) {
            $table->id();
            $table->string('nivel');
            $table->string('institucion');
            $table->string('carrera');
            $table->date('fecha_diploma')->nullable();
            $table->date('fecha_titulo')->nullable();
            $table->foreignId('id_depto')->nullable()->constrained('departamentos', 'id_departamento');
            $table->string('archivo_diploma')->nullable();
            $table->string('archivo_titulo')->nullable();
            $table->foreignUuid('id_persona')->constrained('personas', 'id');
            $table->timestamps();
        });

        // 2. Formacion Postgrado
        Schema::create('formacion_postgrado', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // Especialidad, Maestría, Doctorado, etc.
            $table->string('nombre_programa');
            $table->string('institucion');
            $table->date('fecha_diploma')->nullable();
            $table->date('fecha_certificacion')->nullable();
            $table->string('archivo_respaldo')->nullable();
            $table->foreignId('id_depto')->nullable()->constrained('departamentos', 'id_departamento');
            $table->foreignUuid('id_persona')->constrained('personas', 'id');
            $table->timestamps();
        });

        // 3. Experiencia Docente
        Schema::create('experiencia_docente', function (Blueprint $table) {
            $table->id();
            $table->string('institucion');
            $table->string('carrera');
            $table->text('asignaturas');
            $table->string('gestion_periodo');
            $table->foreignId('id_depto')->nullable()->constrained('departamentos', 'id_departamento');
            $table->string('archivo_respaldo')->nullable();
            $table->foreignUuid('id_persona')->constrained('personas', 'id');
            $table->timestamps();
        });

        // 4. Experiencia Profesional
        Schema::create('experiencia_profesional', function (Blueprint $table) {
            $table->id();
            $table->string('cargo');
            $table->string('empresa');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->foreignId('id_depto')->nullable()->constrained('departamentos', 'id_departamento');
            $table->string('archivo_respaldo')->nullable();
            $table->foreignUuid('id_persona')->constrained('personas', 'id');
            $table->timestamps();
        });

        // 5. Capacitaciones
        Schema::create('capacitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_curso');
            $table->string('institucion');
            $table->date('fecha');
            $table->integer('carga_horaria');
            $table->foreignId('id_depto')->nullable()->constrained('departamentos', 'id_departamento');
            $table->string('archivo_respaldo')->nullable();
            $table->foreignUuid('id_persona')->constrained('personas', 'id');
            $table->timestamps();
        });

        // 6. Producción Intelectual (Libros, Artículos, etc.)
        Schema::create('produccion_intelectual', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // Libro, Artículo, Patente, etc.
            $table->string('titulo');
            $table->date('fecha');
            $table->string('editorial')->nullable();
            $table->foreignId('id_depto')->nullable()->constrained('departamentos', 'id_departamento');
            $table->string('archivo_respaldo')->nullable();
            $table->foreignUuid('id_persona')->constrained('personas', 'id');
            $table->timestamps();
        });

        // 7. Reconocimientos
        Schema::create('reconocimientos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo_premio');
            $table->string('institucion_otorgante');
            $table->date('fecha');
            $table->string('lugar');
            $table->string('archivo_respaldo')->nullable();
            $table->foreignUuid('id_persona')->constrained('personas', 'id');
            $table->timestamps();
        });

        // 8. Idiomas
        Schema::create('idiomas', function (Blueprint $table) {
            $table->id();
            $table->string('idioma');
            $table->string('nivel_habla'); // Básico, intermedio, avanzado, nativo
            $table->string('nivel_escritura');
            $table->string('nivel_lee');
            $table->string('archivo_respaldo')->nullable();
            $table->foreignUuid('id_persona')->constrained('personas', 'id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idiomas');
        Schema::dropIfExists('reconocimientos');
        Schema::dropIfExists('produccion_intelectual');
        Schema::dropIfExists('capacitaciones');
        Schema::dropIfExists('experiencia_profesional');
        Schema::dropIfExists('experiencia_docente');
        Schema::dropIfExists('formacion_postgrado');
        Schema::dropIfExists('formacion_pregrado');
    }
};
