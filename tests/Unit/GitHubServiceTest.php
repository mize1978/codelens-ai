<?php

namespace Tests\Unit;

use App\Services\GitHubService;
use PHPUnit\Framework\TestCase;

class GitHubServiceTest extends TestCase
{
    private GitHubService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GitHubService();
    }

    public function test_parse_url_full_github_url(): void
    {
        $result = $this->service->parseUrl('https://github.com/laravel/framework');
        $this->assertEquals(['owner' => 'laravel', 'repo' => 'framework'], $result);
    }

    public function test_parse_url_owner_slash_repo(): void
    {
        $result = $this->service->parseUrl('laravel/framework');
        $this->assertEquals(['owner' => 'laravel', 'repo' => 'framework'], $result);
    }

    public function test_parse_url_strips_trailing_slash(): void
    {
        $result = $this->service->parseUrl('https://github.com/laravel/framework/');
        $this->assertEquals('framework', $result['repo']);
    }

    public function test_parse_url_throws_for_invalid_input(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->parseUrl('not-a-valid-url');
    }

    public function test_select_key_files_prioritizes_services_over_small_files(): void
    {
        $tree = [
            ['type' => 'blob', 'path' => 'app/Services/ClaudeReviewService.php', 'size' => 5000],
            ['type' => 'blob', 'path' => 'app/Services/GitHubService.php', 'size' => 4500],
            ['type' => 'blob', 'path' => 'app/Http/Controllers/ReviewController.php', 'size' => 3000],
            ['type' => 'blob', 'path' => 'app/Jobs/ProcessReviewJob.php', 'size' => 2000],
            ['type' => 'blob', 'path' => 'app/Models/Review.php', 'size' => 800],
            ['type' => 'blob', 'path' => 'app/Providers/AppServiceProvider.php', 'size' => 100],
            ['type' => 'blob', 'path' => 'app/Http/Controllers/Controller.php', 'size' => 80],
            ['type' => 'blob', 'path' => 'README.md', 'size' => 3000],
            ['type' => 'blob', 'path' => 'composer.json', 'size' => 2000],
        ];

        $selected = $this->service->selectKeyFiles($tree);

        $this->assertContains('app/Services/ClaudeReviewService.php', $selected);
        $this->assertContains('app/Services/GitHubService.php', $selected);
        $this->assertContains('app/Http/Controllers/ReviewController.php', $selected);
    }

    public function test_select_key_files_excludes_vendor(): void
    {
        $tree = [
            ['type' => 'blob', 'path' => 'vendor/laravel/framework/src/Application.php', 'size' => 10000],
            ['type' => 'blob', 'path' => 'app/Services/MyService.php', 'size' => 1000],
        ];

        $selected = $this->service->selectKeyFiles($tree);

        $this->assertNotContains('vendor/laravel/framework/src/Application.php', $selected);
        $this->assertContains('app/Services/MyService.php', $selected);
    }

    public function test_select_key_files_returns_at_most_12(): void
    {
        $tree = [];
        for ($i = 0; $i < 50; $i++) {
            $tree[] = ['type' => 'blob', 'path' => "app/Services/Service{$i}.php", 'size' => 1000 + $i];
        }

        $selected = $this->service->selectKeyFiles($tree);
        $this->assertLessThanOrEqual(12, count($selected));
    }
}
