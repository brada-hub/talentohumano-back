<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nivel_jerarquico', function (Blueprint $table) {
            if (!Schema::hasColumn('nivel_jerarquico', 'activo')) {
                $table->boolean('activo')->default(true)->after('descripcion');
            }
        });

        Schema::table('areas', function (Blueprint $table) {
            if (!Schema::hasColumn('areas', 'activo')) {
                $table->boolean('activo')->default(true)->after('tipo_area');
            }
        });

        Schema::table('cargos', function (Blueprint $table) {
            if (!Schema::hasColumn('cargos', 'activo')) {
                $table->boolean('activo')->default(true)->after('id_nivel_jerarquico');
            }
        });

        Schema::create('grupos_personal', function (Blueprint $table) {
            $table->id('id_grupo_personal');
            $table->string('nombre')->unique();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('cargo_funciones', function (Blueprint $table) {
            $table->id('id_funcion_cargo');
            $table->foreignId('id_cargo')->constrained('cargos', 'id_cargo')->cascadeOnDelete();
            $table->text('descripcion');
            $table->unsignedInteger('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('puestos', function (Blueprint $table) {
            $table->id('id_puesto');
            $table->string('codigo')->nullable()->unique();
            $table->string('nombre_puesto');
            $table->foreignId('id_area')->constrained('areas', 'id_area');
            $table->foreignId('id_cargo')->constrained('cargos', 'id_cargo');
            $table->foreignId('id_grupo_personal')->constrained('grupos_personal', 'id_grupo_personal');
            $table->foreignId('id_sede')->nullable()->constrained('sedes', 'id_sede')->nullOnDelete();
            $table->string('plantilla_contractual')->nullable();
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('puesto_superiores', function (Blueprint $table) {
            $table->id('id_puesto_superior');
            $table->foreignId('id_puesto')->constrained('puestos', 'id_puesto')->cascadeOnDelete();
            $table->foreignId('id_puesto_superior_ref')->constrained('puestos', 'id_puesto')->cascadeOnDelete();
            $table->string('tipo_relacion')->default('Inmediato');
            $table->timestamps();

            $table->unique(['id_puesto', 'id_puesto_superior_ref'], 'uq_puesto_superior');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('puesto_superiores');
        Schema::dropIfExists('puestos');
        Schema::dropIfExists('cargo_funciones');
        Schema::dropIfExists('grupos_personal');

        Schema::table('cargos', function (Blueprint $table) {
            if (Schema::hasColumn('cargos', 'activo')) {
                $table->dropColumn('activo');
            }
        });

        Schema::table('areas', function (Blueprint $table) {
            if (Schema::hasColumn('areas', 'activo')) {
                $table->dropColumn('activo');
            }
        });

        Schema::table('nivel_jerarquico', function (Blueprint $table) {
            if (Schema::hasColumn('nivel_jerarquico', 'activo')) {
                $table->dropColumn('activo');
            }
        });
    }
};
