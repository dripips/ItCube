<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('code');
            $table->string('status');
            $table->longText('output')->nullable();
            $table->json('test_results')->nullable();
            $table->unsignedInteger('passed_count')->default(0);
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('attempt_number')->default(1);
            $table->unsignedInteger('runtime_ms')->default(0);
            $table->timestamps();
            $table->index(['assignment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
