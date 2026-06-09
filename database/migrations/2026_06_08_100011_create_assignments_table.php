<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('test_id');
            $table->uuid('assignee_id');
            $table->uuid('assigned_by');
            $table->timestamp('due_at')->nullable();
            $table->integer('max_attempts')->default(1);
            $table->string('access_type');
            $table->string('access_token')->nullable()->unique();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->foreign('assignee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
            $table->index('tenant_id');
            $table->index('access_token');
            $table->index(['test_id', 'assignee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
