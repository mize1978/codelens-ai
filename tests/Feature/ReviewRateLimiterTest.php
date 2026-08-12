<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Services\ReviewRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * 公開デモの Claude API コストを守るレビュー回数ガード。
 *
 * 契約:
 * - 1 IP あたり 1日 3 回まで（日付単位でリセット・rolling ではない）
 * - サービス全体で 1日 30 回まで（Claude を呼ぶ解析の総数）
 * - キャッシュヒット（analysis_source='cached'／Claude 未呼び出し）はカウントしない
 * - カウントは当日(created_at=today)の analysis_source='generated' のみ
 */
class ReviewRateLimiterTest extends TestCase
{
    use RefreshDatabase;

    private function review(string $ip, string $source, ?Carbon $at = null): Review
    {
        $r = Review::create([
            'github_url'      => 'https://github.com/o/r',
            'owner'           => 'o',
            'repo'            => 'r',
            'status'          => 'complete',
            'analysis_source' => $source,
        ]);
        $r->ip_hash = $ip;
        if ($at) {
            $r->created_at = $at;
        }
        $r->save();

        return $r;
    }

    private function generated(string $ip, ?Carbon $at = null): Review
    {
        return $this->review($ip, 'generated', $at);
    }

    private function cached(string $ip, ?Carbon $at = null): Review
    {
        return $this->review($ip, 'cached', $at);
    }

    public function test_allows_when_no_prior_reviews(): void
    {
        $res = (new ReviewRateLimiter())->check('ipA');
        $this->assertTrue($res['allowed']);
        $this->assertNull($res['reason']);
    }

    public function test_blocks_ip_after_three_generated_today(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->generated('ipA');
        }
        $res = (new ReviewRateLimiter())->check('ipA');
        $this->assertFalse($res['allowed']);
        $this->assertSame('ip', $res['reason']);
    }

    public function test_other_ip_is_not_affected_by_first_ip(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->generated('ipA');
        }
        $res = (new ReviewRateLimiter())->check('ipB');
        $this->assertTrue($res['allowed']);
    }

    public function test_cached_reviews_do_not_count(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->cached('ipA'); // キャッシュ5件は上限に効かない
        }
        $res = (new ReviewRateLimiter())->check('ipA');
        $this->assertTrue($res['allowed']);
    }

    public function test_yesterday_generated_do_not_count(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->generated('ipA', Carbon::yesterday());
        }
        $res = (new ReviewRateLimiter())->check('ipA'); // 日付リセット
        $this->assertTrue($res['allowed']);
    }

    public function test_blocks_globally_after_thirty_generated_today(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->generated('ip' . $i); // 30個の別IP
        }
        $res = (new ReviewRateLimiter())->check('freshIp');
        $this->assertFalse($res['allowed']);
        $this->assertSame('global', $res['reason']);
    }
}
