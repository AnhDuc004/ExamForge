<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('created_by');
            $table->string('type');
            $table->text('content');
            $table->jsonb('options')->nullable();
            $table->jsonb('correct_answer')->nullable();
            $table->integer('max_score')->default(1);
            $table->string('difficulty');
            $table->text('tags')->nullable(); // PostgreSQL text[] array
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('tenant_id');
            $table->index('status');
            $table->index('tags');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
