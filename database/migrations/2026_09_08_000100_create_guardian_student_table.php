<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardian_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('relation')->nullable();
            $table->timestamps();

            // Связь многие-ко-многим намеренно: у ребёнка бывает двое взрослых,
            // а у взрослого — двое детей в разных группах. Одно поле
            // parent_id не покрыло бы ни того, ни другого.
            $table->unique(['guardian_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardian_student');
    }
};
