<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewDeleteCommandTest extends TestCase
{
    use RefreshDatabase;

    /** #7 ④: 管理者 Artisan コマンドで公開Archiveからレビューを削除できる */
    public function test_review_delete_command_removes_review(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'complete',
        ]);

        $this->artisan('review:delete', ['id' => $review->id, '--force' => true])
             ->assertExitCode(0);

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** #7 ④: 存在しないIDは失敗コードを返し、何も壊さない */
    public function test_review_delete_command_reports_missing_id(): void
    {
        $this->artisan('review:delete', ['id' => 999999, '--force' => true])
             ->assertExitCode(1);
    }
}
