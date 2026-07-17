<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'github_url', 'owner', 'repo', 'branch',
        'language', 'quality_score', 'security_score',
        'maintainability_score', 'review_data', 'status', 'ip_hash', 'view_count',
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
}
