<?php

namespace Tests\Feature;

use App\Jobs\ProcessReviewJob;
use App\Models\Review;
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
        $this->post('/reviews', ['github_url' => 'https://github.com/laravel/framework'])
             ->assertRedirect();

        Queue::assertPushed(ProcessReviewJob::class);
        $this->assertDatabaseHas('reviews', [
            'owner'  => 'laravel',
            'repo'   => 'framework',
            'status' => 'pending',
        ]);
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
