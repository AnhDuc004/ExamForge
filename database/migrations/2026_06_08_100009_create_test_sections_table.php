<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->integer('position');

            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->index(['test_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_sections');
    }
};
