<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessReviewJob;
use App\Models\Review;
use App\Services\GitHubService;
use App\Services\ClaudeReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ReviewController extends Controller
{
    public function __construct(
        private GitHubService $github,
        private ClaudeReviewService $claude,
    ) {}

    public function index()
    {
        $popular = Review::where('status', 'complete')
            ->orderBy('view_count', 'desc')->limit(6)->get();
        return view('reviews.index', compact('popular'));
    }

    public function store(Request $request)
    {
        $request->validate(['github_url' => 'required|string|max:500']);

        $limit = (int) config('app.daily_review_limit', 10);
        $key   = 'review:' . hash('sha256', $request->ip());
        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return back()
                ->withErrors(['github_url' => "1日の上限（{$limit}回）に達しました。日付が変わってからお試しください。"])
                ->withInput();
        }
        RateLimiter::hit($key, today()->secondsUntilEndOfDay());
        $ipHash = hash('sha256', $request->ip());

        try {
            ['owner' => $owner, 'repo' => $repo] = $this->github->parseUrl($request->github_url);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['github_url' => '有効なGitHub URLを入力してください'])->withInput();
        }

        $review = Review::create([
            'github_url' => "https://github.com/{$owner}/{$repo}",
            'owner'      => $owner,
            'repo'       => $repo,
            'status'     => 'pending',
            'ip_hash'    => hash('sha256', $request->ip()),
        ]);

        ProcessReviewJob::dispatch($review);

        return redirect()->route('reviews.show', $review);
    }

    public function show(Review $review)
    {
        $review->increment('view_count');
        return view('reviews.show', compact('review'));
    }

    public function status(Review $review)
    {
        return response()->json([
            'status'        => $review->status,
            'progress_step' => $review->progress_step,
        ]);
    }

    public function fix(Request $request, Review $review)
    {
        $key = 'fix:' . hash('sha256', $request->ip());
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json(['status' => 'error', 'message' => '1日の修正提案上限（20回）に達しました。'], 429);
        }
        RateLimiter::hit($key, today()->secondsUntilEndOfDay());

        $request->validate([
            'issue_title' => 'required|string|max:200',
            'issue_desc'  => 'required|string|max:1000',
            'file'        => 'nullable|string|max:255',
        ]);

        try {
            $files = [];
            if ($request->file && $review->review_data) {
                try {
                    $content = $this->github->getFileContent($review->owner, $review->repo, $request->file);
                    $files[$request->file] = $content;
                } catch (\Exception) {}
            }

            $fixed = $this->claude->fixIssue($request->issue_title, $request->issue_desc, $files);
            return response()->json([
                'status'      => 'ok',
                'before'      => $fixed['before'] ?? null,
                'fix'         => $fixed['after']  ?? $fixed['fix'] ?? '',
                'diff'        => $fixed['diff']   ?? null,
                'score_delta' => $fixed['score_delta'] ?? null,
                'explanation' => $fixed['explanation'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function ranking()
    {
        $reviews = Review::where('status', 'complete')
            ->orderBy('view_count', 'desc')->limit(20)->get();
        return view('reviews.ranking', compact('reviews'));
    }
}
