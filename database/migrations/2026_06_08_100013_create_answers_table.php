<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('attempt_id');
            $table->uuid('test_section_question_id');
            $table->jsonb('response')->nullable();
            $table->integer('auto_score')->nullable();
            $table->integer('manual_score')->nullable();
            $table->string('review_status')->default('pending');
            $table->text('reviewer_feedback')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();

            $table->foreign('attempt_id')->references('id')->on('attempts')->onDelete('cascade');
            $table->foreign('test_section_question_id')->references('id')->on('test_section_questions')->onDelete('cascade');
            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
            $table->index('review_status');
            $table->index('attempt_id');
            $table->unique(['attempt_id', 'test_section_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
