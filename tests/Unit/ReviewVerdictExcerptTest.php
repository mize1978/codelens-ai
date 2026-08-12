<?php

namespace Tests\Unit;

use App\Models\Review;
use Tests\TestCase;

/**
 * Recent/Popular Reviews のカードに出す「CodeLens の一言」抜粋。
 * 既存の review_data['one_line_verdict']（保存済み）を使う＝新規 Claude API 呼び出しなし。
 * 長い場合は省略し、詳細ページへの導線にする。
 */
class ReviewVerdictExcerptTest extends TestCase
{
    private function review(array $reviewData): Review
    {
        return new Review(['review_data' => $reviewData]);
    }

    public function test_returns_null_when_no_verdict(): void
    {
        $this->assertNull($this->review([])->verdictExcerpt());
        $this->assertNull($this->review(['one_line_verdict' => ''])->verdictExcerpt());
        $this->assertNull($this->review(['one_line_verdict' => '   '])->verdictExcerpt());
    }

    public function test_returns_short_verdict_untouched(): void
    {
        $v = '再帰的な構造が面白い';
        $this->assertSame($v, $this->review(['one_line_verdict' => $v])->verdictExcerpt(80));
    }

    public function test_truncates_long_verdict_with_ellipsis(): void
    {
        $long = str_repeat('あ', 100);
        $ex   = $this->review(['one_line_verdict' => $long])->verdictExcerpt(20);

        $this->assertStringEndsWith('…', $ex);
        $this->assertSame(21, mb_strlen($ex));        // 20文字 + …
        $this->assertStringStartsWith(mb_substr($long, 0, 20), $ex);
    }
}
