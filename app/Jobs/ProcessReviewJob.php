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

    public int $timeout = 180;
    public int $tries   = 2;

    public function __construct(public Review $review) {}

    public function handle(GitHubService $github, ClaudeReviewService $claude): void
    {
        $review = $this->review;

        try {

            $review->update(['status' => 'processing', 'progress_step' => 'fetching_repository']);

            $stats  = $github->getRepoStats($review->owner, $review->repo);
            $branch = $stats['default_branch'];
            $sha    = $github->getLatestCommitSha($review->owner, $review->repo, $branch);

            // 再現性：同一コミットの完了済みレビューがあればClaudeを呼ばず結果を複製する
            if ($sha) {
                $cached = Review::where('owner', $review->owner)
                    ->where('repo', $review->repo)
                    ->where('commit_sha', $sha)
                    ->where('status', 'complete')
                    ->whereNotNull('review_data')
                    ->where('id', '!=', $review->id)
                    ->latest()
                    ->first();

                if ($cached) {
                    $review->update([
                        'status'                => 'complete',
                        'progress_step'         => null,
                        'commit_sha'            => $sha,
                        'analysis_source'       => 'cached',
                        'cached_from_review_id' => $cached->id,
                        'branch'                => $branch,
                        'language'              => $cached->language,
                        'quality_score'         => $cached->quality_score,
                        'security_score'        => $cached->security_score,
                        'maintainability_score' => $cached->maintainability_score,
                        'review_data'           => $cached->review_data,
                    ]);
                    return;
                }
            }

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
            $data['github_stats']      = $stats;
            $data['analyzed_files']    = array_keys($files);
            $data['total_file_count']  = count($tree);
            $data['selected_file_count'] = count($paths);

            $review->update([
                'status'                => 'complete',
                'progress_step'         => null,
                'language'              => $data['language'] ?? null,
                'quality_score'         => $data['quality_score'] ?? null,
                'security_score'        => $data['security_score'] ?? null,
                'maintainability_score' => $data['maintainability_score'] ?? null,
                'branch'                => $branch,
                'commit_sha'            => $sha,
                'analysis_source'       => 'generated',
                'review_data'           => $data,
            ]);

        } catch (\Exception $e) {
            $review->update([
                'status'        => 'failed',
                'progress_step' => null,
                'error_message' => $this->sanitizeError($e->getMessage()),
            ]);
        }
    }

    private function sanitizeError(string $msg): string
    {
        $msg = preg_replace('/Bearer\s+\S+/i', 'Bearer [REDACTED]', $msg);
        return mb_substr($msg, 0, 200);
    }

    public function failed(\Throwable $e): void
    {
        $this->review->update([
            'status'        => 'failed',
            'progress_step' => null,
            'error_message' => $this->sanitizeError($e->getMessage()),
        ]);
    }
}
