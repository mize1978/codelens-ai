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

        // #7 Public Archive Safety: レビュー結果は公開Archiveに恒久保存されるため、
        // public repository のみ受け付ける。private/存在しないrepoは解析開始前に拒否する。
        // ただし GitHub 側の一時障害（レート制限・5xx・ネットワーク）を「非公開」と誤表示しない。
        try {
            $info = $this->github->getRepoInfo($owner, $repo);
        } catch (\Throwable $e) {
            $status = 0;
            if (preg_match('/GitHub API error:\s*(\d{3})/', $e->getMessage(), $m)) {
                $status = (int) $m[1];
            }
            // 404 = 見つからない、またはアクセス権のない private ＝ ユーザー起因として拒否
            if ($status === 404) {
                return back()->withErrors(['github_url' => 'リポジトリが見つからないか、公開されていません。CodeLensAI は Public Repository のみ対応しています。'])->withInput();
            }
            // それ以外（403 レート制限 / 5xx / 接続断など）は一時障害。原因をログに残し、"非公開" とは別メッセージで案内する。
            \Illuminate\Support\Facades\Log::warning('CodeLens: repository visibility check failed (transient)', [
                'owner'  => $owner,
                'repo'   => $repo,
                'status' => $status,
                'error'  => $e->getMessage(),
            ]);
            return back()->withErrors(['github_url' => 'GitHub に一時的に接続できませんでした。少し時間をおいて再度お試しください。'])->withInput();
        }

        if (($info['private'] ?? false) === true) {
            return back()->withErrors(['github_url' => 'CodeLensAI の公開レビューは Public Repository のみ対応しています。非公開リポジトリはレビューできません。'])->withInput();
        }

        $review = Review::create([
            'github_url' => "https://github.com/{$owner}/{$repo}",
            'owner'      => $owner,
            'repo'       => $repo,
            'status'     => 'pending',
        ]);
        $review->ip_hash = $ipHash;
        $review->save();

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
            if ($request->input('file') && $review->review_data) {
                try {
                    $content = $this->github->getFileContent($review->owner, $review->repo, $request->input('file'));
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
        // 同一リポジトリは最新レビュー1件（最大id＝最新コミットの評価）に集約し、重複表示を防ぐ
        $latestIds = Review::where('status', 'complete')
            ->groupBy('owner', 'repo')
            ->selectRaw('MAX(id) as id')
            ->pluck('id');

        $reviews = Review::whereIn('id', $latestIds)
            ->orderByDesc('view_count')
            ->limit(20)
            ->get();

        return view('reviews.ranking', compact('reviews'));
    }
}
