<?php

namespace Tests\Feature;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_ok(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_store_validates_github_url(): void
    {
        $response = $this->from('/')->withoutMiddleware()
                        ->post('/reviews', ['github_url' => 'not-a-url']);
        $response->assertRedirect('/');
    }

    public function test_store_dispatches_review_for_valid_url(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $response = $this->withoutMiddleware()
                        ->post('/reviews', ['github_url' => 'https://github.com/laravel/framework']);
        $response->assertRedirect();

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessReviewJob::class);

        $this->assertDatabaseHas('reviews', [
            'owner' => 'laravel',
            'repo'  => 'framework',
            'status' => 'pending',
        ]);
    }

    public function test_show_increments_view_count(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'pending',
            'ip_hash'    => hash('sha256', '127.0.0.1'),
        ]);

        $this->get("/reviews/{$review->id}");
        $this->assertEquals(1, $review->fresh()->view_count);
    }

    public function test_status_returns_json(): void
    {
        $review = Review::create([
            'github_url'    => 'https://github.com/test/repo',
            'owner'         => 'test',
            'repo'          => 'repo',
            'status'        => 'processing',
            'progress_step' => 2,
            'ip_hash'       => hash('sha256', '127.0.0.1'),
        ]);

        $response = $this->getJson("/reviews/{$review->id}/status");
        $response->assertStatus(200)
                 ->assertJson(['status' => 'processing', 'progress_step' => 2]);
    }

    public function test_ranking_returns_ok(): void
    {
        $response = $this->get('/ranking');
        $response->assertStatus(200);
    }
}
