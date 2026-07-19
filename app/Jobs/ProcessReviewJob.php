<?php

namespace App\Jobs;

use App\Models\Review;
use App\Services\GitHubService;
use App\Services\ClaudeReviewService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessReviewJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 360;
    public int $tries   = 1;

    public function __construct(public Review $review) {}

    public function handle(): void
    {
        $review = $this->review;

        try {
            $github = new GitHubService();
            $claude = new ClaudeReviewService();

            $review->update(['status' => 'processing', 'progress_step' => 'fetching_repository']);

            $stats  = $github->getRepoStats($review->owner, $review->repo);
            $branch = $stats['default_branch'];
            $tree   = $github->getFileTree($review->owner, $review->repo, $branch);
            $paths  = $github->selectKeyFiles($tree);

            $review->update(['progress_step' => 'reading_files']);

            $files = [];
            foreach ($paths as $path) {
                try {
                    $files[$path] = $github->getFileContent($review->owner, $review->repo, $path);
                } catch (\Exception) {}
            }

            $review->update(['progress_step' => 'analyzing']);
            $data = $claude->review($review->owner, $review->repo, $files);

            $review->update(['progress_step' => 'generating_report']);
            $data['github_stats']    = $stats;
            $data['analyzed_files']  = array_keys($files);

            $review->update([
                'status'                => 'complete',
                'progress_step'         => null,
                'language'              => $data['language'] ?? null,
                'quality_score'         => $data['quality_score'] ?? null,
                'security_score'        => $data['security_score'] ?? null,
                'maintainability_score' => $data['maintainability_score'] ?? null,
                'branch'                => $branch,
                'review_data'           => $data,
            ]);

        } catch (\Exception $e) {
            $review->update([
                'status'        => 'failed',
                'progress_step' => null,
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
