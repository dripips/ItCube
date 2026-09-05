<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('held_on');
            $table->string('status')->default('present');
            $table->string('note')->nullable();
            $table->timestamps();
            // Отметка в схеме 2023 года была парой (ученик, дата) без группы,
            // поэтому у ученика на двух направлениях день был неразличим.
            $table->unique(['group_id', 'user_id', 'held_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
