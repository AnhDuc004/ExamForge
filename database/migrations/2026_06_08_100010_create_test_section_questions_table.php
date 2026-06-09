<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_section_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('section_id');
            $table->uuid('question_id');
            $table->integer('position');
            $table->integer('score_override')->nullable();
            $table->jsonb('question_snapshot');
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('test_sections')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->unique(['section_id', 'question_id']);
            $table->index(['section_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_section_questions');
    }
};
