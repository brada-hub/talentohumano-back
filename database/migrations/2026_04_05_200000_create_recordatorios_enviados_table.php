<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordatorios_enviados', function (Blueprint $table) {
            $table->id('id_recordatorio');
            $table->unsignedBigInteger('id_empleado')->nullable();
            $table->string('id_persona')->nullable();
            $table->string('tipo', 50);
            $table->string('canal', 30)->default('correo');
            $table->string('destinatario')->nullable();
            $table->string('asunto')->nullable();
            $table->date('fecha_evento')->nullable();
            $table->boolean('automatico')->default(false);
            $table->string('estado', 30)->default('pendiente');
            $table->timestamp('enviado_en')->nullable();
            $table->text('error')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'fecha_evento']);
            $table->index(['id_empleado', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios_enviados');
    }
};
