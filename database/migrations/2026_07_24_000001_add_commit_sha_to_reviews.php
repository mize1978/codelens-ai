<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // 解析対象コミットのSHA。同一コミットの再レビューをキャッシュ（再現性）
            $table->string('commit_sha', 40)->nullable()->after('branch');
            $table->index(['owner', 'repo', 'commit_sha']);
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['owner', 'repo', 'commit_sha']);
            $table->dropColumn('commit_sha');
        });
    }
};
