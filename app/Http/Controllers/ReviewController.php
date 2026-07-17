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
            ->orderBy('view_count', 'desc')
            ->limit(6)
            ->get();

        return view('reviews.index', compact('popular'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'github_url' => 'required|string|max:500',
        ]);

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

            // Get repo info
            $repoInfo = $github->getRepoInfo($review->owner, $review->repo);
            $branch   = $repoInfo['default_branch'] ?? 'main';

            // Get file tree and select key files
            $tree     = $github->getFileTree($review->owner, $review->repo, $branch);
            $paths    = $github->selectKeyFiles($tree);

            // Fetch file contents
            $files = [];
            foreach ($paths as $path) {
                try {
                    $files[$path] = $github->getFileContent($review->owner, $review->repo, $path);
                } catch (\Exception) {
                    // Skip unreadable files
                }
            }

            // Claude review
            $data = $claude->review($review->owner, $review->repo, $files);

            $review->update([
                'status'                 => 'complete',
                'language'               => $data['language'] ?? null,
                'quality_score'          => $data['quality_score'] ?? null,
                'security_score'         => $data['security_score'] ?? null,
                'maintainability_score'  => $data['maintainability_score'] ?? null,
                'branch'                 => $branch,
                'review_data'            => $data,
            ]);

            return response()->json(['status' => 'complete', 'data' => $data]);

        } catch (\Exception $e) {
            $review->update(['status' => 'failed']);
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }

    public function ranking()
    {
        $reviews = Review::where('status', 'complete')
            ->orderBy('view_count', 'desc')
            ->limit(20)
            ->get();

        return view('reviews.ranking', compact('reviews'));
    }
}
