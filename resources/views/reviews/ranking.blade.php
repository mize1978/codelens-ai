@extends('layouts.app')
@section('title', 'ランキング — CodeLens AI')

@section('content')
<div style="max-width:740px;margin:0 auto;padding:28px 20px 60px">

  {{-- Hero --}}
  <div style="text-align:center;margin-bottom:38px">
    <div id="ranking-crown-wrap">
      <img src="/images/icon-ranking.png" id="ranking-crown">
    </div>
    <h1 style="font-size:1.6rem;font-weight:800;letter-spacing:0.05em;margin-bottom:6px">RANKING</h1>
    <p style="font-size:0.78rem;color:var(--text-dim)">最も注目されたリポジトリ TOP {{ $reviews->count() }}</p>
  </div>

  @if($reviews->count())
  <div style="display:flex;flex-direction:column;gap:10px">
    @foreach($reviews as $i => $review)
    @php
      $overall  = $review->overall_score;
      $color    = $review->score_color;
      $lang     = $review->language ?? ($review->review_data['language'] ?? null);
      $fw       = $review->review_data['framework'] ?? null;
      $secScore = $review->security_score ?? 0;
      $qlScore  = $review->quality_score ?? 0;

      // グレード + グレード色
      [$grade, $gradeColor] = match(true) {
        $overall >= 90 => ['S', '#00d4ff'],
        $overall >= 80 => ['A', '#00ff88'],
        $overall >= 70 => ['B', '#4488ff'],
        $overall >= 60 => ['C', '#ffcc00'],
        $overall >= 50 => ['D', '#ff8844'],
        default        => ['F', '#ff4466'],
      };

      // 称号バッジ（優先順）
      [$title, $titleColor, $titleBg] = match(true) {
        $i === 0          => ['👑 Hall of Fame',    '#ffd700', 'rgba(255,215,0,0.12)'],
        $secScore >= 85   => ['🛡 Security Master', '#c084fc', 'rgba(180,80,255,0.12)'],
        $qlScore >= 85    => ['💎 Clean Code',      '#60c8ff', 'rgba(0,150,255,0.12)'],
        $overall >= 85    => ['⚡ Top Rated',        '#00ff88', 'rgba(0,255,136,0.1)'],
        $i === 1          => ['🚀 Rising Star',      '#aaa',    'rgba(255,255,255,0.06)'],
        $i === 2          => ['🔥 Fan Favorite',     '#ff8844', 'rgba(255,120,40,0.1)'],
        $review->view_count >= 10 => ['👀 Trending', '#88aaff', 'rgba(100,150,255,0.08)'],
        default           => [null, null, null],
      };

      $medals = ['🥇','🥈','🥉'];
      $medal  = $medals[$i] ?? null;
      $isFirst = $i === 0;
    @endphp

    <a href="{{ route('reviews.show', $review) }}"
       class="rank-card {{ $isFirst ? 'rank-first' : '' }}"
       style="{{ $isFirst ? 'border-color:rgba(255,210,50,0.5);background:rgba(255,200,40,0.06);' : '' }}">

      {{-- Hall of Fame バッジ（1位のみ、左上） --}}
      @if($isFirst)
      <div class="hof-badge">👑 Hall of Fame</div>
      @elseif($title)
      <div class="title-badge" style="color:{{ $titleColor }};background:{{ $titleBg }}">{{ $title }}</div>
      @endif

      {{-- 順位 --}}
      <div class="rank-pos">
        @if($isFirst)
          <img src="/images/cl-trophy.png" class="rank-trophy-img" alt="#1">
        @elseif($medal)
          <span class="rank-medal">{{ $medal }}</span>
        @else
          <span class="rank-num">#{{ $i + 1 }}</span>
        @endif
      </div>

      {{-- リポジトリ名 + タグ --}}
      <div class="rank-info">
        <div class="rank-repo">{{ $review->owner }}/<strong>{{ $review->repo }}</strong></div>
        <div class="rank-tags">
          @if($lang)<span class="rank-tag">{{ $lang }}</span>@endif
          @if($fw && $fw !== 'Unknown' && $fw !== 'なし')<span class="rank-tag">{{ $fw }}</span>@endif
        </div>
        @if($isFirst)
        <div class="rank-approved">CodeLens Approved</div>
        @php
          $congrats = ['いいコード、見つけた…！', 'ずっと見てたよ。', 'おめでとうっ！', 'さすがだよ…！'];
        @endphp
        <div class="rank-congrats">
          <img src="/images/cl-scroll.png" class="congrats-img" alt="">
          <span>「{{ $congrats[array_rand($congrats)] }}」</span>
        </div>
        @endif
      </div>

      {{-- グレード + スコア + バー --}}
      <div class="rank-score-wrap">
        <div class="rank-grade" style="color:{{ $gradeColor }}">{{ $grade }}</div>
        <div class="rank-score-num" style="color:{{ $gradeColor }}">{{ $overall }}</div>
        <div class="rank-bar-bg">
          <div class="rank-bar-fill" style="width:{{ $overall }}%;background:{{ $gradeColor }};box-shadow:0 0 6px {{ $gradeColor }}88"></div>
        </div>
      </div>

      {{-- 閲覧数 --}}
      <div class="rank-views">👁 {{ number_format($review->view_count) }} views</div>

    </a>
    @endforeach
  </div>
  @else
    <div style="text-align:center;padding:60px 20px;color:var(--text-mute);font-size:0.85rem">
      まだレビューがありません
    </div>
  @endif

  <div style="margin-top:32px;text-align:center">
    <a href="{{ route('reviews.index') }}" class="btn btn-primary">⚡ Analyze Your Repository</a>
  </div>

</div>

<style>
/* ─── Crown icon ─── */
#ranking-crown-wrap {
  position: relative; display: inline-block;
  margin-bottom: 16px; overflow: hidden;
  border-radius: 50%;
}
#ranking-crown {
  width: 160px; height: 160px; object-fit: contain; display: block;
  margin-bottom: -16px;
  opacity: 0.85;
  filter: drop-shadow(0 0 10px rgba(120,90,255,0.3));
  transition: opacity 0.35s, transform 0.35s;
}
#ranking-crown-wrap:hover #ranking-crown {
  opacity: 0.8;
  transform: scale(1.05);
}
/* キラッ — 王冠エリア（上40%）だけ光が走る */
#ranking-crown-wrap::after {
  content: '';
  position: absolute;
  top: 0; left: -60%; width: 45%; height: 45%;
  background: linear-gradient(110deg,
    transparent 20%,
    rgba(255,230,120,0.55) 50%,
    transparent 80%);
  pointer-events: none; opacity: 0;
}
#ranking-crown-wrap.glinting::after {
  animation: crownGlint 0.65s ease-out forwards;
}
@keyframes crownGlint {
  0%   { left: -60%; opacity: 0; }
  15%  { opacity: 1; }
  100% { left: 130%; opacity: 0; }
}

/* ─── Card ─── */
.rank-card {
  position: relative; overflow: hidden;
  display: flex; align-items: center; gap: 14px;
  padding: 16px 18px;
  background: var(--bg3); border: 1px solid var(--border);
  border-radius: 12px; text-decoration: none;
  transition: border-color 0.25s, transform 0.2s, box-shadow 0.2s;
}
.rank-card:hover {
  border-color: rgba(0,200,255,0.4);
  transform: translateX(4px);
  box-shadow: 0 4px 24px rgba(0,0,0,0.35);
}

/* ─── 1位 aurora ─── */
.rank-first { border-width: 1.5px; }
.rank-first::before {
  content: '';
  position: absolute; inset: 0; pointer-events: none;
  background: linear-gradient(120deg,
    rgba(0,150,255,0.12) 0%,
    rgba(160,0,255,0.14) 50%,
    rgba(0,150,255,0.12) 100%);
  background-size: 300% 100%;
  opacity: 0; transition: opacity 0.4s;
}
.rank-first:hover::before {
  opacity: 1;
  animation: auroraSlide 3s linear infinite;
}
@keyframes auroraSlide {
  from { background-position: -100% 0; }
  to   { background-position: 200% 0; }
}

/* ─── Hall of Fame バッジ ─── */
.hof-badge {
  position: absolute; top: 0; left: 0;
  font-size: 0.55rem; font-weight: 800; letter-spacing: 0.18em;
  color: #c8960c;
  background: linear-gradient(90deg, rgba(255,215,0,0.2), rgba(255,215,0,0.06));
  border-bottom: 1px solid rgba(255,210,50,0.3);
  border-right: 1px solid rgba(255,210,50,0.2);
  border-radius: 12px 0 10px 0;
  padding: 4px 10px;
  text-shadow: 0 0 8px rgba(255,200,0,0.6);
}
.title-badge {
  position: absolute; top: 6px; right: 10px;
  font-size: 0.58rem; font-weight: 700; letter-spacing: 0.1em;
  padding: 3px 9px; border-radius: 5px;
}

/* ─── 順位 ─── */
.rank-pos { min-width: 72px; text-align: center; flex-shrink: 0; }
.rank-trophy-img {
  width: 72px; height: 72px; object-fit: contain;
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.45));
  display: block; margin: 32px auto 0;
}
.rank-first .rank-pos { align-self: flex-start; }
.rank-medal { font-size: 1.5rem; line-height: 1; display: block; }
.rank-num { font-size: 0.85rem; font-weight: 800; color: var(--text-mute); font-family: monospace; }

/* ─── Info ─── */
.rank-info { flex: 1; min-width: 0; }
.rank-repo { font-size: 0.82rem; color: var(--text-dim); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 5px; }
.rank-repo strong { color: #fff; }
.rank-tags { display: flex; gap: 5px; flex-wrap: wrap; }
.rank-tag  { font-size: 0.58rem; padding: 2px 7px; border-radius: 4px; background: rgba(0,200,255,0.07); border: 1px solid rgba(0,200,255,0.14); color: var(--text-mute); font-weight: 600; }

/* ─── Score ─── */
.rank-score-wrap { min-width: 96px; text-align: right; flex-shrink: 0; }
.rank-grade      { font-size: 0.6rem; font-weight: 900; letter-spacing: 0.15em; margin-bottom: 1px; opacity: 0.9; }
.rank-score-num  { font-size: 1.15rem; font-weight: 900; font-family: monospace; line-height: 1; margin-bottom: 6px; }
.rank-bar-bg     { height: 3px; background: rgba(255,255,255,0.07); border-radius: 2px; overflow: hidden; width: 100%; }
.rank-bar-fill   { height: 100%; border-radius: 2px; transition: width 0.8s cubic-bezier(0.16,1,0.3,1); }

/* ─── Recognized ─── */
.rank-recognized {
  margin-top: 5px; font-size: 0.6rem; color: rgba(255,200,50,0.5);
  letter-spacing: 0.06em; font-style: italic;
}
.rank-approved {
  display: inline-block; margin-top: 5px;
  font-size: 0.58rem; font-weight: 700; letter-spacing: 0.1em;
  color: #c8960c;
  background: rgba(255,210,50,0.1); border: 1px solid rgba(255,210,50,0.22);
  border-radius: 4px; padding: 2px 7px;
}
.rank-congrats {
  display: flex; align-items: center; gap: 5px;
  margin-top: 14px; font-size: 0.62rem;
  color: var(--text-dim); font-style: italic;
  letter-spacing: 0.02em;
}
.congrats-img {
  width: 22px; height: 22px; object-fit: contain; flex-shrink: 0;
  filter: drop-shadow(0 1px 4px rgba(0,0,0,0.3));
}

/* ─── Views ─── */
.rank-views { font-size: 0.68rem; color: var(--text-mute); min-width: 64px; text-align: right; flex-shrink: 0; white-space: nowrap; }
</style>

<script>
(function() {
  // 王冠だけ6秒ごとにキラッ（アイコン全体は動かさない）
  const wrap = document.getElementById('ranking-crown-wrap');
  function glint() {
    wrap.classList.add('glinting');
    setTimeout(() => wrap.classList.remove('glinting'), 700);
  }
  setTimeout(() => { glint(); setInterval(glint, 6000); }, 2000);

  // スコアバーを遅延アニメーション
  document.querySelectorAll('.rank-bar-fill').forEach((bar, i) => {
    const w = bar.style.width;
    bar.style.width = '0';
    setTimeout(() => { bar.style.width = w; }, 120 + i * 80);
  });
})();
</script>
@endsection
