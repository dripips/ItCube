<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('instructions')->nullable();
            $table->string('language');
            $table->longText('starter_code')->nullable();
            $table->longText('solution_code')->nullable();
            $table->json('hints')->nullable();
            $table->string('difficulty')->default('easy');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
            $table->unique(['lesson_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
