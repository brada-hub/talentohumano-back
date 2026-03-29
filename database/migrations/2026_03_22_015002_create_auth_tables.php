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
        // Module: Auth
        Schema::create('sistemas', function (Blueprint $table) {
            $table->id('id_sistema');
            $table->string('sistema');
            $table->string('url_sistema');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id('id_permision');
            $table->string('nombres');
            $table->foreignId('sistema_id')->constrained('sistemas', 'id_sistema');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombres');
            $table->foreignId('sistema_id')->constrained('sistemas', 'id_sistema');
            $table->timestamps();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles', 'id_rol')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions', 'id_permision')->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->uuid('id_persona')->nullable(); // FK to personas (UUID)
            $table->string('username')->unique();
            $table->string('password');
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('user_has_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users', 'id_user')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles', 'id_rol')->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('username')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_has_roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('sistemas');
    }
};
