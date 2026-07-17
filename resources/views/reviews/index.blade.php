@extends('layouts.app')
@section('title', 'CodeLens AI — GitHubリポジトリをAIレビュー')

@section('content')
<div style="max-width:860px;margin:0 auto;padding:22px 20px 60px">

  {{-- Hero --}}
  <div style="text-align:center;margin-bottom:36px">
    <p class="ai-system-label">AI CODE REVIEW SYSTEM v1.0</p>
    <div style="display:flex;align-items:center;justify-content:center;gap:16px;margin-bottom:10px">
      <img src="/images/devinsight-logo.png" id="hero-logo" style="width:56px;height:56px;border-radius:14px;filter:drop-shadow(0 0 10px rgba(0,140,255,0.35))" alt="logo">
      <h1 class="hero-logo-text" style="margin-bottom:0">CodeLens AI</h1>
    </div>
    <p style="font-size:0.88rem;color:var(--text-dim)">GitHubリポジトリURLを入力するだけで、AIがコードを解析してレビューします</p>
    <div class="lang-chips">
      @foreach(['Rails','Laravel','React','Next.js','Python','Go','TypeScript','Vue'] as $lang)
      <span class="lang-chip">{{ $lang }}</span>
      @endforeach
    </div>
  </div>

  {{-- Input form --}}
  <div class="panel" style="margin-bottom:28px">
    <div class="panel-header">
      <span>REPOSITORY INPUT</span>
      <div class="wdots">
        <div class="wd" style="background:#ff5f57"></div>
        <div class="wd" style="background:#febc2e"></div>
        <div class="wd" style="background:#28c840"></div>
      </div>
    </div>
    <div style="padding:20px">
      @if($errors->any())
        <p style="color:var(--red);font-size:0.78rem;margin-bottom:12px">{{ $errors->first() }}</p>
      @endif
      <form action="{{ route('reviews.store') }}" method="POST" id="review-form">
        @csrf
        <p style="font-size:0.65rem;color:var(--text-mute);letter-spacing:0.15em;margin-bottom:8px">GITHUB URL</p>
        <div style="display:flex;gap:10px">
          <div class="input-wrap" style="flex:1">
            <input type="text" name="github_url" class="input-field" style="width:100%"
              placeholder="https://github.com/owner/repo  または  owner/repo"
              value="{{ old('github_url') }}" autocomplete="off" required>
            <div class="input-scan-beam"></div>
          </div>
          <button type="submit" class="btn btn-primary" id="submit-btn" style="white-space:nowrap;min-width:130px">
            ⚡ レビュー開始
          </button>
        </div>
        <p style="font-size:0.65rem;color:var(--text-mute);margin-top:8px">
          ※ GitHubトークン設定で大きなリポジトリや非公開リポジトリにも対応可
        </p>
      </form>
    </div>
  </div>

  {{-- Feature cards --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:32px">

    {{-- ⑤ 解析 → 青 --}}
    <div class="panel feature-card fc-analysis">
      <div class="feature-icon-wrap icon-analysis">
        <img src="/images/icon-analysis.png" alt="analysis">
        {{-- ③ スキャンライン overlay --}}
        <div class="scan-line"></div>
      </div>
      <div class="feature-title">高速解析</div>
      <div class="feature-desc">ファイルツリーを自動スキャンし、重要ファイルを選択してレビュー</div>
    </div>

    {{-- ⑤ セキュリティ → 紫 --}}
    <div class="panel feature-card fc-security">
      <div class="feature-icon-wrap icon-security">
        <img src="/images/icon-security.png" alt="security">
        {{-- ③ チェックマーク overlay --}}
        <svg class="check-overlay" viewBox="0 0 24 24" fill="none">
          <path class="check-path" d="M5 13l4 4L19 7" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="28" stroke-dashoffset="28"/>
        </svg>
      </div>
      <div class="feature-title">セキュリティ</div>
      <div class="feature-desc">脆弱性・セキュリティリスクをAIが検出して深刻度順に警告</div>
    </div>

    {{-- ⑤ リファクタ → 青紫 --}}
    <div class="panel feature-card fc-refactor">
      <div class="feature-icon-wrap icon-refactor">
        <img src="/images/icon-refactor.png" alt="refactor">
        {{-- ③ 線が左から右へ流れる --}}
        <svg class="branch-overlay" viewBox="0 0 50 12" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="branchGrad" x1="0" y1="0" x2="1" y2="0">
              <stop offset="0%" stop-color="#4488ff"/>
              <stop offset="100%" stop-color="#9944ff"/>
            </linearGradient>
          </defs>
          <polyline points="2,6 43,6 39,3 43,6 39,9"
            stroke="url(#branchGrad)" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </svg>
      </div>
      <div class="feature-title">リファクタ</div>
      <div class="feature-desc">具体的なコード改善提案と品質・保守性スコアを提示</div>
    </div>

  </div>

  {{-- CodeLens Library --}}
  <div class="library-section">

    {{-- タイトルレール: ─── CodeLens Library ─── --}}
    <div class="lib-title-rail">
      <span class="lib-title">CodeLens Library</span>
    </div>
    <p class="lib-subtitle">Every project has its own story.</p>

    {{-- 本 + 棚板 --}}
    <div class="lib-shelf-wrap">
      <div class="library-shelf">

        <a class="lib-book" href="#" target="_blank" rel="noopener">
          <img src="/images/library/book-codelens.png" alt="CodeLens" class="lib-book-img">
          <span class="lib-book-name">CodeLens</span>
        </a>

        <a class="lib-book" href="#" target="_blank" rel="noopener">
          <img src="/images/library/book-design-bible.png" alt="Design Bible" class="lib-book-img">
          <span class="lib-book-name">Design Bible</span>
        </a>

        <a class="lib-book" href="#" target="_blank" rel="noopener">
          <img src="/images/library/book-knowledge.png" alt="Knowledge" class="lib-book-img">
          <span class="lib-book-name">Knowledge</span>
        </a>

        <a class="lib-book" href="#" target="_blank" rel="noopener">
          <img src="/images/library/book-projects.png" alt="Projects" class="lib-book-img">
          <span class="lib-book-name">Projects</span>
        </a>

        <a class="lib-book" href="#" target="_blank" rel="noopener">
          <img src="/images/library/book-ideas.png" alt="Ideas" class="lib-book-img">
          <span class="lib-book-name">Ideas</span>
        </a>

        <a class="lib-book lib-book--archive" href="#" target="_blank" rel="noopener">
          <img src="/images/library/book-archive.png" alt="Archive" class="lib-book-img">
          <span class="lib-book-name">Archive</span>
        </a>

      </div>

      {{-- 棚板: ◎────────────────◎ --}}
      <div class="lib-shelf-board"></div>
    </div>

  </div>

  {{-- Popular Reviews --}}
  @if($popular->count())
  <div>
    <p class="section-title">⭐ Popular Reviews</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px">
      @foreach($popular as $review)
      @php
        $stars = max(1, min(5, round($review->overall_score / 20)));
        $fw = $review->review_data['framework'] ?? null;
        $lang = $review->language ?? ($review->review_data['language'] ?? null);
        $circleMap = ['#00ff88' => 'sc-green','#4488ff' => 'sc-blue','#ffaa00' => 'sc-yellow','#ff4466' => 'sc-red'];
        $circleClass = $circleMap[$review->score_color] ?? 'sc-blue';
      @endphp
      <a href="{{ route('reviews.show', $review) }}" class="popular-card">
        <div class="pc-top">
          <span class="pc-repo">{{ $review->owner }}/<strong>{{ $review->repo }}</strong></span>
          {{-- ④ 円バッジ --}}
          <span class="score-circle {{ $circleClass }}">{{ $review->overall_score }}</span>
        </div>
        <div class="pc-stars">
          @for($i = 1; $i <= 5; $i++)
            <span style="color:{{ $i <= $stars ? '#ffcc00' : 'rgba(255,255,255,0.15)' }}">★</span>
          @endfor
        </div>
        <div class="pc-tags">
          @if($lang)<span class="pc-tag">{{ $lang }}</span>@endif
          @if($fw && $fw !== 'Unknown' && $fw !== 'なし')<span class="pc-tag">{{ $fw }}</span>@endif
          <span class="pc-tag pc-views">👁 {{ $review->view_count }}</span>
        </div>
      </a>
      @endforeach
    </div>
  </div>
  @endif

</div>

<style>
/* ===== Feature cards ===== */
.feature-card {
  padding: 22px 18px; text-align: center;
  transition: border-color 0.25s, transform 0.25s, box-shadow 0.25s;
}
/* ② hover — 浮かせる */
.feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.4); }

/* ⑤ カード色分け */
.fc-analysis { border-color: rgba(0,180,255,0.2); }
.fc-analysis:hover { border-color: rgba(0,180,255,0.55); box-shadow: 0 12px 32px rgba(0,150,255,0.15); }

.fc-security { border-color: rgba(153,68,255,0.2); }
.fc-security:hover { border-color: rgba(153,68,255,0.55); box-shadow: 0 12px 32px rgba(153,68,255,0.15); }

.fc-refactor { border-color: rgba(80,100,255,0.2); }
.fc-refactor:hover { border-color: rgba(80,100,255,0.55); box-shadow: 0 12px 32px rgba(80,100,255,0.15); }

/* ① アイコンサイズ 74px + ② ホバーで拡大 */
.feature-icon-wrap {
  width: 74px; height: 74px; margin: 0 auto 16px;
  position: relative; overflow: visible;
  transition: transform 0.25s;
}
.feature-card:hover .feature-icon-wrap {
  transform: translateY(-3px) scale(1.05);
}
.feature-icon-wrap img {
  width: 100%; height: 100%; object-fit: contain;
  transition: filter 0.3s;
}
.fc-analysis:hover .feature-icon-wrap img  { filter: drop-shadow(0 0 10px rgba(0,180,255,0.45)); }
.fc-security:hover .feature-icon-wrap img  { filter: drop-shadow(0 0 10px rgba(153,68,255,0.45)); }
.fc-refactor:hover .feature-icon-wrap img  { filter: drop-shadow(0 0 10px rgba(80,100,255,0.45)); }

.feature-title { font-size: 0.82rem; font-weight: 700; margin-bottom: 6px; color: #fff; }
.feature-desc { font-size: 0.68rem; color: var(--text-dim); line-height: 1.6; }

/* ③ 解析 — スキャンライン */
.scan-line {
  position: absolute; top: 48%; left: -100%;
  width: 100%; height: 2px;
  background: linear-gradient(90deg, transparent, rgba(0,220,255,0.9) 50%, transparent);
  pointer-events: none; opacity: 0;
}
.fc-analysis:hover .scan-line {
  animation: scanAnim 0.55s ease-out forwards;
}
@keyframes scanAnim {
  0%   { left: -100%; opacity: 1; }
  100% { left: 110%;  opacity: 0; }
}

/* ③ セキュリティ — チェック描画 */
.check-overlay {
  position: absolute; bottom: -2px; right: -2px;
  width: 22px; height: 22px; overflow: visible; pointer-events: none;
}
.check-path { transition: none; }
.fc-security:hover .check-path {
  animation: drawCheck 0.5s ease-out 0.05s forwards;
}
@keyframes drawCheck {
  to { stroke-dashoffset: 0; }
}

/* ③ リファクタ — 線が流れる (clip-path reveal) */
.branch-overlay {
  position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
  width: 54px; height: 14px; overflow: visible; pointer-events: none;
  clip-path: inset(0 100% 0 0);
}
.fc-refactor:hover .branch-overlay {
  animation: revealLine 0.5s ease-out forwards;
}
@keyframes revealLine {
  from { clip-path: inset(0 100% 0 0); }
  to   { clip-path: inset(0 -4px 0 0); }
}

/* ④ 円バッジ */
.score-circle {
  width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem; font-weight: 900; font-family: monospace;
  border: 1.5px solid;
}
.sc-green  { color: #00ff88; border-color: rgba(0,255,136,0.5);  background: rgba(0,255,136,0.08); }
.sc-blue   { color: #4488ff; border-color: rgba(68,136,255,0.5); background: rgba(68,136,255,0.08); }
.sc-yellow { color: #ffaa00; border-color: rgba(255,170,0,0.5);  background: rgba(255,170,0,0.08); }
.sc-red    { color: #ff4466; border-color: rgba(255,68,102,0.5); background: rgba(255,68,102,0.08); }

/* 対応言語チップ */
.lang-chips { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-top: 14px; }
.lang-chip {
  font-size: 0.65rem; padding: 3px 10px; border-radius: 99px;
  background: rgba(0,200,255,0.06); border: 1px solid rgba(0,200,255,0.14);
  color: var(--text-mute); letter-spacing: 0.06em; font-weight: 500;
}

/* 入力欄スキャンビーム */
.input-wrap { position: relative; overflow: hidden; border-radius: 6px; }
.input-scan-beam {
  position: absolute; top: 0; left: -60%;
  width: 55%; height: 100%;
  background: linear-gradient(90deg, transparent 0%, rgba(0,200,255,0.18) 50%, transparent 100%);
  pointer-events: none; opacity: 0;
}
.input-wrap.scanning .input-scan-beam {
  animation: inputBeam 0.55s ease-out forwards;
}
@keyframes inputBeam {
  0%   { left: -60%; opacity: 1; }
  100% { left: 110%; opacity: 0.6; }
}

/* Popular cards */
.popular-card {
  display: block; padding: 14px 16px;
  background: var(--bg3); border: 1px solid var(--border);
  border-radius: 10px; transition: border-color 0.2s, transform 0.2s;
  text-decoration: none;
}
.popular-card:hover {
  border-color: rgba(0,200,255,0.45);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,150,255,0.1);
}
.pc-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px; gap: 8px; }
.pc-repo { font-size: 0.78rem; color: var(--text-dim); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pc-repo strong { color: #fff; }
.pc-stars { font-size: 0.8rem; letter-spacing: 1px; margin-bottom: 8px; }
.pc-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.pc-tag {
  font-size: 0.62rem; padding: 2px 7px; border-radius: 4px;
  background: rgba(0,200,255,0.08); border: 1px solid rgba(0,200,255,0.15);
  color: var(--text-dim); font-weight: 600; letter-spacing: 0.05em;
}
.pc-views { background: transparent; border-color: transparent; color: var(--text-mute); }

/* ===== CodeLens Library ===== */
.library-section {
  margin: 0 -12px 48px;
  padding: 0 12px;
}

/* ─── CodeLens Library ─── */
.lib-title-rail {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}
.lib-title-rail::before,
.lib-title-rail::after {
  content: '';
  flex: 1;
  height: 1px;
}
.lib-title-rail::before {
  background: linear-gradient(90deg, transparent, rgba(0,200,255,0.22));
}
.lib-title-rail::after {
  background: linear-gradient(270deg, transparent, rgba(0,200,255,0.22));
}
.lib-title {
  padding: 0 16px;
  font-size: 0.62rem; font-weight: 700; letter-spacing: 0.35em;
  color: rgba(255,255,255,0.38);
  text-transform: uppercase; white-space: nowrap;
}
.lib-subtitle {
  font-size: 0.60rem; color: rgba(255,255,255,0.18);
  letter-spacing: 0.06em; text-align: center;
  margin: 6px 0 14px; font-style: italic;
}

/* Shelf wrap */
.lib-shelf-wrap { position: relative; }

/* Books grid */
.library-shelf {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 6px;
  align-items: end;
  padding-bottom: 6px;
}

/* ◎──────────────◎ 棚板 */
.lib-shelf-board {
  position: relative;
  height: 2px;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(0,185,255,0.18) 5%,
    rgba(0,205,255,0.58) 50%,
    rgba(0,185,255,0.18) 95%,
    transparent 100%
  );
  box-shadow: 0 0 14px rgba(0,200,255,0.16), 0 1px 1px rgba(0,200,255,0.10);
}
/* ◎ 端のキャップ */
.lib-shelf-board::before,
.lib-shelf-board::after {
  content: '';
  position: absolute;
  top: 50%; transform: translateY(-50%);
  width: 7px; height: 7px;
  border-radius: 50%;
  background: rgba(0,215,255,0.95);
  box-shadow: 0 0 8px rgba(0,200,255,0.85), 0 0 20px rgba(0,200,255,0.40);
}
.lib-shelf-board::before { left: 0; }
.lib-shelf-board::after  { right: 0; }

/* Each book */
.lib-book {
  display: flex; flex-direction: column; align-items: center; gap: 7px;
  text-decoration: none;
}

/* Book image */
.lib-book-img {
  width: 100%; display: block; border-radius: 6px;
  transition: transform 0.22s ease, filter 0.22s ease;
  will-change: transform, filter;
  transform-origin: center bottom;
}

/* ホバー: 浮く + 棚板に影が落ちる + 金縁グロー */
.lib-book:hover .lib-book-img {
  transform: translateY(-8px) scale(1.03);
  filter:
    drop-shadow(0 22px 8px rgba(0,0,0,0.52))    /* 棚板への影 */
    drop-shadow(0 0 10px rgba(185,148,52,0.28)); /* 金縁グロー */
}

/* Book label */
.lib-book-name {
  font-size: 0.58rem; font-weight: 600; letter-spacing: 0.06em;
  color: var(--text-mute); text-align: center;
  transition: color 0.2s;
}
.lib-book:hover .lib-book-name { color: var(--text-dim); }

/* Archive — 静かに眠っている */
.lib-book--archive .lib-book-img {
  filter: saturate(0.25) brightness(0.62);
}
.lib-book--archive:hover .lib-book-img {
  transform: translateY(-5px) scale(1.01);
  transition: transform 0.35s ease, filter 0.35s ease;
  filter: saturate(0.38) brightness(0.75); /* 金縁グローなし */
}

/* Responsive */
@media (max-width: 640px) {
  .library-shelf { grid-template-columns: repeat(3, 1fr); gap: 8px; }
  .library-section { margin: 0 0 44px; padding: 0; }
}
@media (max-width: 380px) {
  .library-shelf { grid-template-columns: repeat(2, 1fr); }
}
</style>

<script>
document.getElementById('review-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  const btn  = document.getElementById('submit-btn');
  const inputWrap = document.querySelector('.input-wrap');

  btn.style.minWidth = btn.offsetWidth + 'px';

  // 入力欄にスキャンビームを走らせる
  inputWrap.classList.add('scanning');

  // ステータス更新
  const repoVal = document.getElementById('repo-input')?.value || document.querySelector('input[name="repo"]')?.value || '';
  if (window.CodeLensStatus) window.CodeLensStatus.set('reviewing', { repo: repoVal });

  // ボタンシーケンス: ○ → ◎ → AI Analyzing...
  btn.textContent = '○';
  setTimeout(() => { btn.textContent = '◎'; }, 220);
  setTimeout(() => {
    btn.textContent = 'AI Analyzing...';
    btn.classList.add('btn-reviewing');
    if (window.CodeLensStatus) window.CodeLensStatus.set('thinking');
  }, 520);

  // ロゴスピン
  document.querySelectorAll('.logo-icon, #hero-logo').forEach(el => el.classList.add('spinning'));

  // アニメ完了後にサブミット
  setTimeout(() => form.submit(), 860);
});
</script>
@endsection
