<?php

namespace Tests\Feature;

use App\Jobs\ProcessReviewJob;
use App\Models\Review;
use App\Services\GitHubService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('review:' . hash('sha256', '127.0.0.1'));
        RateLimiter::clear('fix:' . hash('sha256', '127.0.0.1'));
    }

    public function test_index_returns_ok(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_ranking_returns_ok(): void
    {
        $this->get('/ranking')->assertStatus(200);
    }

    public function test_store_validates_github_url(): void
    {
        $this->post('/reviews', ['github_url' => ''])
             ->assertRedirect();
    }

    public function test_store_rejects_non_github_url(): void
    {
        Queue::fake();
        $this->post('/reviews', ['github_url' => 'https://example.com/owner/repo'])
             ->assertSessionHasErrors('github_url');
    }

    public function test_store_dispatches_job_for_valid_url(): void
    {
        Queue::fake();

        // #7: store() は public 判定のため getRepoInfo を呼ぶ。テストはネットワークに出さず public を返す。
        $this->partialMock(GitHubService::class, function ($mock) {
            $mock->shouldReceive('getRepoInfo')->andReturn(['private' => false]);
        });

        $this->post('/reviews', ['github_url' => 'https://github.com/laravel/framework'])
             ->assertRedirect();

        Queue::assertPushed(ProcessReviewJob::class);
        $this->assertDatabaseHas('reviews', [
            'owner'  => 'laravel',
            'repo'   => 'framework',
            'status' => 'pending',
        ]);
    }

    /** #7 Public Archive Safety ②: private repo は解析開始前に拒否（公開Archiveに載せない） */
    public function test_store_rejects_private_repository_before_analysis(): void
    {
        Queue::fake();

        // GitHub API が private=true を返す状況。parseUrl は本物のまま。
        $this->partialMock(GitHubService::class, function ($mock) {
            $mock->shouldReceive('getRepoInfo')->andReturn(['private' => true]);
        });

        $this->post('/reviews', ['github_url' => 'https://github.com/mize1978/secret-private'])
             ->assertSessionHasErrors('github_url');

        // 拒否なので Review は作られず、解析ジョブも発火しない
        $this->assertDatabaseCount('reviews', 0);
        Queue::assertNotPushed(ProcessReviewJob::class);
    }

    /** #7 Public Archive Safety ③: 存在しない／アクセス不能 repo（404等）も適切に拒否 */
    public function test_store_rejects_inaccessible_repository(): void
    {
        Queue::fake();

        // getRepoInfo が 404 相当で例外を投げる状況
        $this->partialMock(GitHubService::class, function ($mock) {
            $mock->shouldReceive('getRepoInfo')
                 ->andThrow(new \RuntimeException('GitHub API error: 404 for /repos/ghost/nope'));
        });

        $this->post('/reviews', ['github_url' => 'https://github.com/ghost/nope'])
             ->assertSessionHasErrors('github_url');

        $this->assertDatabaseCount('reviews', 0);
        Queue::assertNotPushed(ProcessReviewJob::class);
    }

    /** #7 Public Archive Safety: GitHub の一時障害（レート制限/5xx）を「非公開」と誤表示しない */
    public function test_store_treats_github_outage_as_transient_not_private(): void
    {
        Queue::fake();

        // 403（レート制限相当）で例外。private ではなく一時障害。
        $this->partialMock(GitHubService::class, function ($mock) {
            $mock->shouldReceive('getRepoInfo')
                 ->andThrow(new \RuntimeException('GitHub API error: 403 for /repos/some/repo'));
        });

        $this->post('/reviews', ['github_url' => 'https://github.com/some/repo'])
             ->assertSessionHasErrors('github_url');

        // 一時障害でも公開Archiveには載せない
        $this->assertDatabaseCount('reviews', 0);
        Queue::assertNotPushed(ProcessReviewJob::class);

        // メッセージは「一時的」と案内し、"非公開"/"Public" と誤認させない
        $message = implode(' ', session('errors')->get('github_url'));
        $this->assertStringContainsString('一時的', $message);
        $this->assertStringNotContainsString('非公開', $message);
    }

    public function test_store_enforces_daily_rate_limit(): void
    {
        Queue::fake();
        $key   = 'review:' . hash('sha256', '127.0.0.1');
        $limit = (int) config('app.daily_review_limit', 10);
        for ($i = 0; $i < $limit; $i++) {
            RateLimiter::hit($key, today()->secondsUntilEndOfDay());
        }

        $this->post('/reviews', ['github_url' => 'https://github.com/laravel/framework'])
             ->assertSessionHasErrors('github_url');
    }

    public function test_show_increments_view_count(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'pending',
        ]);

        $this->get("/reviews/{$review->id}");
        $this->assertEquals(1, $review->fresh()->view_count);
    }

    public function test_status_returns_pending(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'pending',
        ]);

        $this->getJson("/reviews/{$review->id}/status")
             ->assertOk()
             ->assertJson(['status' => 'pending', 'progress_step' => null]);
    }

    public function test_status_returns_complete(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'complete',
        ]);

        $this->getJson("/reviews/{$review->id}/status")
             ->assertOk()
             ->assertJson(['status' => 'complete']);
    }

    public function test_status_returns_failed(): void
    {
        $review = Review::create([
            'github_url'    => 'https://github.com/test/repo',
            'owner'         => 'test',
            'repo'          => 'repo',
            'status'        => 'failed',
            'error_message' => 'Claude API error: 429',
        ]);

        $this->getJson("/reviews/{$review->id}/status")
             ->assertOk()
             ->assertJson(['status' => 'failed']);
    }

    public function test_fix_validates_required_fields(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'complete',
        ]);

        $this->postJson("/reviews/{$review->id}/fix", [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['issue_title', 'issue_desc']);
    }

    public function test_fix_rejects_oversized_title(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'complete',
        ]);

        $this->postJson("/reviews/{$review->id}/fix", [
                 'issue_title' => str_repeat('a', 201),
                 'issue_desc'  => 'desc',
             ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['issue_title']);
    }

    public function test_fix_enforces_rate_limit(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'complete',
        ]);

        $key = 'fix:' . hash('sha256', '127.0.0.1');
        for ($i = 0; $i < 20; $i++) {
            RateLimiter::hit($key, today()->secondsUntilEndOfDay());
        }

        $this->postJson("/reviews/{$review->id}/fix", [
                 'issue_title' => 'Test Issue',
                 'issue_desc'  => 'Test description',
             ])
             ->assertStatus(429);
    }
}
