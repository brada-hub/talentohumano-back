<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Habilitar el portal por defecto
        DB::table('_configs')->insert([
            'key' => 'onboarding_enabled',
            'value' => '1',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('_configs');
    }
};
