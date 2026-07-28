<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

// 🏠 本館：Workspace v2 を home に（旧 v1 ランディングは /v1 展示室へ保存）
Route::get('/', function () {
    $popular = \App\Models\Review::where('status', 'complete')
        ->orderBy('view_count', 'desc')->limit(6)->get();
    return view('reviews.index_preview', compact('popular'));
})->name('reviews.index');

// 🏛 v1 展示室：旧ランディング（設計の変遷アーカイブとして保存・削除しない）
Route::get('/v1', [ReviewController::class, 'index'])->name('legacy.v1');
Route::post('/reviews',                  [ReviewController::class, 'store'])->name('reviews.store');
Route::get('/reviews/{review}',          [ReviewController::class, 'show'])->name('reviews.show');
Route::get('/reviews/{review}/status',   [ReviewController::class, 'status'])->name('reviews.status');
Route::post('/reviews/{review}/fix',     [ReviewController::class, 'fix'])->name('reviews.fix');
Route::get('/ranking',                   [ReviewController::class, 'ranking'])->name('ranking');

// 🚪 プレビュー専用（コピーの index_preview を表示）。本番 / は無傷のまま見比べる用。
Route::get('/preview', function () {
    $popular = \App\Models\Review::where('status', 'complete')
        ->orderBy('view_count', 'desc')->limit(6)->get();
    return view('reviews.index_preview', compact('popular'));
})->name('preview');

// 🛸 preview 専用：in-place Analyze（本物のjobを発火してJSONでidを返す。本番storeは無傷）
Route::post('/preview/analyze', function (\Illuminate\Http\Request $request) {
    $request->validate(['github_url' => 'required|string|max:500']);
    try {
        ['owner' => $owner, 'repo' => $repo] = app(\App\Services\GitHubService::class)->parseUrl($request->github_url);
    } catch (\InvalidArgumentException $e) {
        return response()->json(['error' => '有効なGitHub URLを入力してください'], 422);
    }
    $review = \App\Models\Review::create([
        'github_url' => "https://github.com/{$owner}/{$repo}",
        'owner' => $owner, 'repo' => $repo, 'status' => 'pending',
    ]);
    $review->ip_hash = hash('sha256', $request->ip());
    $review->save();
    \App\Jobs\ProcessReviewJob::dispatch($review);
    return response()->json([
        'id'         => $review->id,
        'status_url' => route('reviews.status', $review),
        'result_url' => route('reviews.show', $review),
    ]);
})->name('preview.analyze');

// 🛸 preview 専用：完了レビューの結果を console.html の finish() 形式で返す（score/grade/color/entry）
Route::get('/preview/result/{review}', function (\App\Models\Review $review) {
    $score = (int) $review->overall_score;
    $grade = $score >= 80 ? 'EXCELLENT' : ($score >= 60 ? 'GOOD' : ($score >= 40 ? 'FAIR' : 'NEEDS WORK'));
    $color = $score >= 60 ? '#3fb950' : ($score >= 40 ? '#d6a531' : '#f2585a');
    $rd = $review->review_data ?? [];
    return response()->json([
        'score'      => $score,
        'grade'      => $grade,
        'color'      => $color,
        'entry'      => '#' . $review->id,
        'repo'       => $review->repo,
        'insight'    => $rd['one_line_verdict'] ?? ($rd['summary'] ?? null),   // 本物のAI総評（辛辣な名刺）
        'result_url' => route('reviews.show', $review),
    ]);
})->name('preview.result');

// 🗂 preview 専用：Review Report 本編（v1 show の中身を partial 化・スコアヒーロー除去）をHTMLで返す
Route::get('/preview/report/{review}', function (\App\Models\Review $review) {
    return view('reviews._report', compact('review'));
})->name('preview.report');

// 📰 Articles ＝ 建物の最初の部屋（Repository Intelligence Journal）。新規・加算のみ。
Route::get('/articles', function () {
    return view('reviews.articles');
})->name('articles');

// ✍️ 代表作（Manifesto）：Experience First 本文。思想が実際に置かれた最初の記事。
Route::get('/articles/experience-first', function () {
    return view('reviews.articles.experience-first');
})->name('articles.experience-first');

// ✍️ Articles 02：Repository Intelligence（AIは何を読んで、何を覚えるのか）
Route::get('/articles/repository-intelligence', function () {
    return view('reviews.articles.repository-intelligence');
})->name('articles.repository-intelligence');

// ✍️ Articles 03：Reading Code（レビューを「採点」でなく「読書体験」に変えた設計）
Route::get('/articles/reading-code', function () {
    return view('reviews.articles.reading-code');
})->name('articles.reading-code');

// ✍️ Articles 04（最終章）：Information Flow（理解はどう生まれるのか＝理解は設計できる）
Route::get('/articles/information-flow', function () {
    return view('reviews.articles.information-flow');
})->name('articles.information-flow');

// 📚 Reviews フロア＝Repository Memory の本棚。GET /reviews（本番の POST /reviews・show・ranking は無傷）。
Route::get('/reviews', function (\Illuminate\Http\Request $request) {
    // 開館の日＝本当に最初の一冊(total===1) だけ三幕。once-only は front(localStorage)。?receiving=1 はデモ（毎回再生）。?empty=1 は Waiting 確認。
    $demo  = $request->boolean('receiving');
    $total = \App\Models\Review::where('status', 'complete')->count();

    if ($request->boolean('empty')) {
        $reviews = collect();
        $opening = false;
    } else {
        $reviews = \App\Models\Review::where('status', 'complete')->orderByDesc('created_at')->limit(40)->get();
        $opening = $demo || $total === 1;   // 2冊目以降は false ＝演出なし・棚だけ
    }

    $incoming = $opening ? \App\Models\Review::where('status', 'complete')->latest()->first() : null;

    return view('reviews.archive', compact('reviews', 'incoming', 'opening', 'demo'));
})->name('review.archive');

// 📘 Docs フロア＝Engineering Reference（実装仕様。「読ませる部屋」でなく「探しに来る部屋」）。
Route::get('/docs', fn () => view('reviews.docs.index'))->name('docs');
Route::get('/docs/{page}', function (string $page) {
    abort_unless(in_array($page, ['architecture', 'repository-memory', 'prompt-design', 'scoring', 'api'], true), 404);
    return view('reviews.docs.' . $page);
})->name('docs.page');
