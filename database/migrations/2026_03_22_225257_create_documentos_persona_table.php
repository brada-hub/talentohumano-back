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
        Schema::create('documentos_persona', function (Blueprint $table) {
            $table->id('id');
            $table->uuid('id_persona');
            $table->enum('tipo', ['ci', 'pasaporte', 'cert_nacimiento', 'otro']);
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('formato')->nullable(); // pdf o imagen
            $table->timestamps();

            $table->foreign('id_persona')->references('id')->on('personas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_persona');
    }
};
