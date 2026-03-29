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
        Schema::create('parentesco', function (Blueprint $table) {
            $table->id('id_parentesco');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::create('beneficiarios', function (Blueprint $table) {
            $table->id('id_beneficiario');
            $table->foreignId('id_empleado')->constrained('empleados', 'id_empleado');
            $table->string('primer_apellido');
            $table->string('segundo_apellido')->nullable();
            $table->string('nombres');
            $table->string('ci', 15)->nullable();
            $table->string('complemento', 5)->nullable();
            $table->foreignId('id_ci_expedido')->nullable()->constrained('departamentos', 'id_departamento');
            $table->date('fecha_nacimiento');
            $table->foreignId('id_parentesco')->constrained('parentesco', 'id_parentesco');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiarios');
        Schema::dropIfExists('parentesco');
    }
};
