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
        // Catalogs for HR
        Schema::create('caja_salud', function (Blueprint $table) {
            $table->id('id_caja');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('entidad_pensiones', function (Blueprint $table) {
            $table->id('id_entidad_pensiones');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('tipo_contrato', function (Blueprint $table) {
            $table->id('id_tipo_contrato');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('nivel_jerarquico', function (Blueprint $table) {
            $table->id('id_jerarquico');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        Schema::create('cargos', function (Blueprint $table) {
            $table->id('id_cargo');
            $table->string('nombre_cargo');
            $table->string('descripcion')->nullable();
            $table->foreignId('id_nivel_jerarquico')->constrained('nivel_jerarquico', 'id_jerarquico');
            $table->timestamps();
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id('id_area');
            $table->string('nombre_area');
            $table->foreignId('id_area_padre')->nullable()->constrained('areas', 'id_area');
            $table->string('tipo_area'); // e.g. "Administrativa", "Academica"
            $table->timestamps();
        });

        Schema::create('sedes', function (Blueprint $table) {
            $table->id('id_sede');
            $table->string('nombre');
            $table->foreignId('id_ciudad')->constrained('ciudades', 'id_ciudad');
            $table->timestamps();
        });

        // Core HR Tables
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('id_empleado');
            $table->uuid('id_persona')->unique();
            $table->foreign('id_persona')->references('id')->on('personas');
            $table->string('celular_institucional', 15)->nullable();
            $table->string('correo_institucional')->nullable();
            $table->foreignId('id_caja')->nullable()->constrained('caja_salud', 'id_caja');
            $table->string('nro_matricula_seguro')->nullable();
            $table->foreignId('id_entidad_pensiones')->nullable()->constrained('entidad_pensiones', 'id_entidad_pensiones');
            $table->string('nro_nua_cua')->nullable();
            $table->string('estado_laboral')->default('Activo'); // Enum: Activo, Inactivo, etc.
            $table->timestamps();
        });

        Schema::create('contratos', function (Blueprint $table) {
            $table->id('id_contrato');
            $table->foreignId('id_empleado')->constrained('empleados', 'id_empleado');
            $table->foreignId('id_tipo_contrato')->constrained('tipo_contrato', 'id_tipo_contrato');
            $table->foreignId('id_area')->constrained('areas', 'id_area');
            $table->foreignId('id_cargo')->constrained('cargos', 'id_cargo');
            $table->decimal('salario', 12, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->foreignId('id_sede')->constrained('sedes', 'id_sede');
            $table->enum('estado_contrato', ['Activo', 'Vencido', 'Rescindido'])->default('Activo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
        Schema::dropIfExists('empleados');
        Schema::dropIfExists('sedes');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('cargos');
        Schema::dropIfExists('nivel_jerarquico');
        Schema::dropIfExists('tipo_contrato');
        Schema::dropIfExists('entidad_pensiones');
        Schema::dropIfExists('caja_salud');
    }
};
