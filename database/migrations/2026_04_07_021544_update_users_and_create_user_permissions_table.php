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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_sede_scope')->nullable()->after('id_persona');
            $table->foreign('id_sede_scope')->references('id_sede')->on('sedes')->onDelete('set null');
        });

        Schema::create('user_has_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users', 'id_user')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions', 'id_permision')->onDelete('cascade');
            $table->primary(['user_id', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_has_permissions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_sede_scope']);
            $table->dropColumn('id_sede_scope');
        });
    }
};
