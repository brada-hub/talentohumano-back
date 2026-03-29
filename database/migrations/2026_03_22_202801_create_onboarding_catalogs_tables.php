<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Niveles Pregrado
        Schema::create('cat_nivel_pregrado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        DB::table('cat_nivel_pregrado')->insert([
            ['nombre' => 'Técnico Medio', 'created_at' => now()],
            ['nombre' => 'Técnico Superior', 'created_at' => now()],
            ['nombre' => 'Licenciatura', 'created_at' => now()],
            ['nombre' => 'Grado', 'created_at' => now()]
        ]);

        // 2. Tipos Postgrado 
        Schema::create('cat_tipo_postgrado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->timestamps();
        });

        DB::table('cat_tipo_postgrado')->insert([
            ['nombre' => 'Diplomado', 'created_at' => now()],
            ['nombre' => 'Especialización', 'created_at' => now()],
            ['nombre' => 'Maestría', 'created_at' => now()],
            ['nombre' => 'Doctorado', 'created_at' => now()],
            ['nombre' => 'Post-Doctorado', 'created_at' => now()]
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_nivel_pregrado');
        Schema::dropIfExists('cat_tipo_postgrado');
    }
};
