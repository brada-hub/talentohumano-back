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
        \Illuminate\Support\Facades\DB::statement("SET FOREIGN_KEY_CHECKS=0;");
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE personas 
                MODIFY id_ci_expedido BIGINT UNSIGNED NULL,
                MODIFY id_sexo BIGINT UNSIGNED NULL,
                MODIFY celular_personal VARCHAR(15) NULL,
                MODIFY correo_personal VARCHAR(255) NULL,
                MODIFY estado_civil VARCHAR(255) NULL,
                MODIFY id_nacionalidad BIGINT UNSIGNED NULL,
                MODIFY direccion_domicilio VARCHAR(255) NULL,
                MODIFY id_ciudad BIGINT UNSIGNED NULL,
                MODIFY id_pais BIGINT UNSIGNED NULL
        ");
        \Illuminate\Support\Facades\DB::statement("SET FOREIGN_KEY_CHECKS=1;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep nullable for safety
    }
};
