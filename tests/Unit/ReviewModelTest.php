<?php

namespace Tests\Unit;

use App\Models\Review;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;

    private function makeReview(int $q, int $s, int $m): Review
    {
        return Review::create([
            'github_url'            => 'https://github.com/test/repo',
            'owner'                 => 'test',
            'repo'                  => 'repo',
            'status'                => 'complete',
            'quality_score'         => $q,
            'security_score'        => $s,
            'maintainability_score' => $m,
        ]);
    }

    public function test_overall_score_is_average_of_three(): void
    {
        $review = $this->makeReview(90, 60, 90);
        $this->assertEquals(80, $review->overall_score);
    }

    public function test_score_label_excellent(): void
    {
        $review = $this->makeReview(90, 90, 90);
        $this->assertEquals('EXCELLENT', $review->score_label);
    }

    public function test_score_label_good(): void
    {
        $review = $this->makeReview(70, 60, 70);
        $this->assertEquals('GOOD', $review->score_label);
    }

    public function test_score_label_fair(): void
    {
        $review = $this->makeReview(50, 40, 40);
        $this->assertEquals('FAIR', $review->score_label);
    }

    public function test_score_label_needs_work(): void
    {
        $review = $this->makeReview(30, 30, 30);
        $this->assertEquals('NEEDS WORK', $review->score_label);
    }

    public function test_score_color_for_excellent(): void
    {
        $review = $this->makeReview(90, 90, 90);
        $this->assertEquals('#00ff88', $review->score_color);
    }

    public function test_score_color_for_good(): void
    {
        $review = $this->makeReview(70, 60, 70);
        $this->assertEquals('#4488ff', $review->score_color);
    }

    public function test_ip_hash_not_mass_assignable(): void
    {
        $review = Review::create([
            'github_url' => 'https://github.com/test/repo',
            'owner'      => 'test',
            'repo'       => 'repo',
            'status'     => 'pending',
            'ip_hash'    => 'should-be-ignored',
        ]);
        $this->assertNull($review->ip_hash);
    }
}
