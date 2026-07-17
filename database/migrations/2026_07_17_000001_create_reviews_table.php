<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('github_url');
            $table->string('owner');
            $table->string('repo');
            $table->string('branch')->default('main');
            $table->string('language')->nullable();
            $table->integer('quality_score')->nullable();
            $table->integer('security_score')->nullable();
            $table->integer('maintainability_score')->nullable();
            $table->json('review_data')->nullable();
            $table->string('status')->default('pending');
            $table->string('ip_hash')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
