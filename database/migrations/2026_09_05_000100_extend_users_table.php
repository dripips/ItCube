<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Старый ItCube держал преподавателей и студентов в двух таблицах
            // с двумя отдельными входами. Здесь один вход и роль полем.
            $table->string('username')->unique()->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('role')->default('student')->index()->after('last_name');
            $table->string('avatar_path')->nullable();
            $table->text('bio')->nullable();
            $table->string('locale', 5)->nullable();
            $table->boolean('is_active')->default(true);
        });

        // Ученику младших групп почта не нужна, вход у него по логину.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'first_name', 'last_name', 'role', 'avatar_path', 'bio', 'locale', 'is_active']);
        });
    }
};
