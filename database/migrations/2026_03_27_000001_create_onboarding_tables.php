<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla de tokens para onboarding
        Schema::create('onboarding_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token', 64)->unique();
            $table->uuid('id_persona')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('usado_en')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('id_persona')->references('id')->on('personas')->onDelete('cascade');
            $table->foreign('created_by')->references('id_user')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_tokens');
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn('estado_onboarding');
        });
    }
};
