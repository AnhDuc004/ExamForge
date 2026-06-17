<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('attempts', 'created_at')) {
                $table->timestamps();
            }
        });

        Schema::table('answers', function (Blueprint $table) {
            if (!Schema::hasColumn('answers', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            if (Schema::hasColumn('answers', 'created_at')) {
                $table->dropTimestamps();
            }
        });

        Schema::table('attempts', function (Blueprint $table) {
            if (Schema::hasColumn('attempts', 'created_at')) {
                $table->dropTimestamps();
            }
        });
    }
};
