<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'github_url', 'owner', 'repo', 'branch', 'commit_sha',
        'analysis_source', 'cached_from_review_id',
        'language', 'quality_score', 'security_score',
        'maintainability_score', 'review_data', 'status',
        'progress_step', 'error_message', 'view_count',
    ];

    protected $casts = [
        'review_data' => 'array',
    ];

    public function getOverallScoreAttribute(): int
    {
        return (int) round(
            (($this->quality_score ?? 0) + ($this->security_score ?? 0) + ($this->maintainability_score ?? 0)) / 3
        );
    }

    public function getScoreLabelAttribute(): string
    {
        $score = $this->overall_score;
        if ($score >= 80) return 'EXCELLENT';
        if ($score >= 60) return 'GOOD';
        if ($score >= 40) return 'FAIR';
        return 'NEEDS WORK';
    }

    public function getScoreColorAttribute(): string
    {
        $score = $this->overall_score;
        if ($score >= 80) return '#00ff88';
        if ($score >= 60) return '#4488ff';
        if ($score >= 40) return '#ffaa00';
        return '#ff4466';
    }

    /**
     * カード用の「CodeLens の一言」抜粋。保存済みの one_line_verdict を使い（新規 API 呼び出しなし）、
     * 長い場合は文字数基準で省略する。詳細ページで全文を見せる導線にする。
     */
    public function verdictExcerpt(int $limit = 80): ?string
    {
        $v = $this->review_data['one_line_verdict'] ?? null;
        if (! is_string($v) || trim($v) === '') {
            return null;
        }
        $v = trim($v);

        return mb_strlen($v) <= $limit ? $v : mb_substr($v, 0, $limit) . '…';
    }
}
