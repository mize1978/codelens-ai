<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Carbon;

/**
 * 公開デモの Claude API コストを守るレビュー回数ガード。
 *
 * - 1 IP あたり 1日 PER_IP_DAILY 回まで（日付単位でリセット）
 * - サービス全体で 1日 GLOBAL_DAILY 回まで
 * - カウントは当日(created_at=today)の analysis_source='generated' のみ
 *   （キャッシュヒット='cached' や上限で止めた='limited' は Claude を呼ばないのでカウントしない）
 */
class ReviewRateLimiter
{
    public const PER_IP_DAILY = 3;
    public const GLOBAL_DAILY = 30;

    /**
     * 新しく「Claude を呼ぶ解析」を実行してよいか。
     *
     * @param  string    $ipHash    sha256(IP)
     * @param  int|null  $excludeId 自レビューをカウントから除外する（job 内チェック用）
     * @return array{allowed:bool, reason:?string}  reason は 'ip' | 'global' | null
     */
    public function check(string $ipHash, ?int $excludeId = null): array
    {
        $today = Review::whereDate('created_at', Carbon::today())
            ->where('analysis_source', 'generated');

        if ($excludeId !== null) {
            $today->where('id', '!=', $excludeId);
        }

        if ((clone $today)->count() >= self::GLOBAL_DAILY) {
            return ['allowed' => false, 'reason' => 'global'];
        }

        if ((clone $today)->where('ip_hash', $ipHash)->count() >= self::PER_IP_DAILY) {
            return ['allowed' => false, 'reason' => 'ip'];
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function message(): string
    {
        return '本日の解析上限に達しました。明日またお試しください';
    }
}
