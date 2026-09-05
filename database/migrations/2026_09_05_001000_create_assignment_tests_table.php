<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->text('stdin')->nullable();
            $table->text('expected_output');
            // Скрытый кейс ученику не показывают: иначе решение подгоняют под
            // известный ответ вместо того, чтобы заставить его работать вообще.
            $table->boolean('is_hidden')->default(false);
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_tests');
    }
};
