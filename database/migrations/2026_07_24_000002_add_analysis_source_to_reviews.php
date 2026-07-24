<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // generated = Claudeで実解析 / cached = 同一コミットの既存結果を複製
            $table->string('analysis_source', 20)->default('generated')->after('commit_sha');
            // 複製元レビューID（cachedのときのみ）。原本の追跡用
            $table->unsignedBigInteger('cached_from_review_id')->nullable()->after('analysis_source');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['analysis_source', 'cached_from_review_id']);
        });
    }
};
