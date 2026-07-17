<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\GitHubService;
use App\Services\ClaudeReviewService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        $popular = Review::where('status', 'complete')
            ->orderBy('view_count', 'desc')->limit(6)->get();
        return view('reviews.index', compact('popular'));
    }

    public function store(Request $request)
    {
        $request->validate(['github_url' => 'required|string|max:500']);

        $github = new GitHubService();
        try {
            ['owner' => $owner, 'repo' => $repo] = $github->parseUrl($request->github_url);
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

        return redirect()->route('reviews.show', $review);
    }

    public function show(Review $review)
    {
        $review->increment('view_count');
        return view('reviews.show', compact('review'));
    }

    public function process(Review $review)
    {
        if ($review->status === 'complete') {
            return response()->json(['status' => 'complete', 'data' => $review->review_data]);
        }

        $review->update(['status' => 'processing']);

        try {
            $github = new GitHubService();
            $claude = new ClaudeReviewService();

            // GitHub stats + repo info
            $stats  = $github->getRepoStats($review->owner, $review->repo);
            $branch = $stats['default_branch'];

            // File tree + key files
            $tree  = $github->getFileTree($review->owner, $review->repo, $branch);
            $paths = $github->selectKeyFiles($tree);

            $files = [];
            foreach ($paths as $path) {
                try {
                    $files[$path] = $github->getFileContent($review->owner, $review->repo, $path);
                } catch (\Exception) {}
            }

            // Claude review
            $data = $claude->review($review->owner, $review->repo, $files);
            $data['github_stats'] = $stats;
            $data['analyzed_files'] = array_keys($files);

            $review->update([
                'status'                => 'complete',
                'language'              => $data['language'] ?? null,
                'quality_score'         => $data['quality_score'] ?? null,
                'security_score'        => $data['security_score'] ?? null,
                'maintainability_score' => $data['maintainability_score'] ?? null,
                'branch'                => $branch,
                'review_data'           => $data,
            ]);

            return response()->json([
                'status'  => 'complete',
                'data'    => $data,
                'latency' => $claude->lastLatency,
                'files'   => count($files),
            ]);

        } catch (\Exception $e) {
            $review->update(['status' => 'failed']);
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function fix(Request $request, Review $review)
    {
        $request->validate([
            'issue_title' => 'required|string',
            'issue_desc'  => 'required|string',
            'file'        => 'nullable|string',
        ]);

        try {
            $github = new GitHubService();
            $claude = new ClaudeReviewService();

            $files = [];
            if ($request->file && $review->review_data) {
                try {
                    $content = $github->getFileContent($review->owner, $review->repo, $request->file);
                    $files[$request->file] = $content;
                } catch (\Exception) {}
            }

            $fixed = $claude->fixIssue($request->issue_title, $request->issue_desc, $files);
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
