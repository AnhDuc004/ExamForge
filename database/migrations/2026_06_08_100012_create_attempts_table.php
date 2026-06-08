<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('assignment_id');
            $table->uuid('assignee_id');
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at');
            $table->string('status')->default('in_progress');
            $table->integer('auto_score')->nullable();
            $table->integer('manual_score')->nullable();
            $table->integer('total_score')->nullable();
            $table->boolean('is_passed')->nullable();
            $table->boolean('is_finalized')->default(false);

            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->foreign('assignee_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('assignment_id');
            $table->index('assignee_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
