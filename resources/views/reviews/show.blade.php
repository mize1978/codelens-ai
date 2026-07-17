@extends('layouts.app')
@section('title', $review->owner.'/'.$review->repo.' — CodeLens AI')

@section('head')
<meta name="review-id" content="{{ $review->id }}">
<meta name="review-status" content="{{ $review->status }}">
@endsection

@section('content')
<div style="max-width:1100px;margin:0 auto;padding:20px" id="review-wrap">

  <!-- Breadcrumb -->
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-size:0.72rem;color:var(--text-mute)">
    <a href="{{ route('reviews.index') }}" style="color:var(--cyan)">CodeLens AI</a>
    <span>/</span>
    <a href="{{ $review->github_url }}" target="_blank" style="color:var(--text-dim)">{{ $review->owner }}/{{ $review->repo }}</a>
  </div>

  @if($review->status !== 'complete')
  <!-- Processing state -->
  <div id="processing-view" class="panel" style="padding:40px;text-align:center">
    <div id="process-icon" style="font-size:2.5rem;margin-bottom:16px">⚙️</div>
    <h2 style="font-size:1.1rem;margin-bottom:8px" id="process-title">AIレビューを実行中...</h2>
    <p id="process-step" style="font-size:0.78rem;color:var(--text-dim);margin-bottom:24px">リポジトリを解析しています...</p>

    <div style="max-width:400px;margin:0 auto">
      @foreach(['GitHubからファイルツリーを取得中...', '重要ファイルを選択中...', 'AIがコードを解析中...', 'レビュー結果を生成中...'] as $step)
      <div class="console-line" style="text-align:left;font-size:0.75rem;color:var(--text-dim);margin-bottom:4px;opacity:0;transition:opacity 0.3s">
        <span style="color:var(--cyan);margin-right:8px">&gt;</span> {{ $step }}
      </div>
      @endforeach
    </div>

    <div style="margin-top:24px;height:4px;background:rgba(255,255,255,0.05);border-radius:2px;overflow:hidden;max-width:400px;margin-left:auto;margin-right:auto">
      <div id="progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,var(--cyan),var(--blue));border-radius:2px;transition:width 0.5s ease"></div>
    </div>
  </div>
  @endif

  <!-- Result state (hidden until complete) -->
  <div id="result-view" class="{{ $review->status !== 'complete' ? 'hidden' : '' }}">
    @if($review->status === 'complete' && $review->review_data)
    @php $data = $review->review_data; @endphp

    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px">
          <h1 style="font-size:1.3rem;font-weight:900">{{ $review->owner }}/{{ $review->repo }}</h1>
          <span class="badge badge-{{ $review->score_color }}">{{ $review->score_label }}</span>
          @if($data['language'] ?? null)
          <span class="badge badge-blue">{{ $data['language'] }}</span>
          @endif
          @if($data['framework'] ?? null)
          <span class="badge badge-blue">{{ $data['framework'] }}</span>
          @endif
        </div>
        <p style="font-size:0.78rem;color:var(--text-dim)">{{ $data['summary'] ?? '' }}</p>
      </div>
      <a href="{{ $review->github_url }}" target="_blank" class="btn btn-primary" style="font-size:0.75rem;padding:8px 16px">
        GitHub で見る →
      </a>
    </div>

    <!-- 3-col layout -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">

      <!-- Score: Quality -->
      @foreach([
        ['品質スコア', $review->quality_score, 'quality'],
        ['セキュリティ', $review->security_score, 'security'],
        ['保守性', $review->maintainability_score, 'maintain'],
      ] as [$label, $score, $key])
      @php
        $clr = $score >= 80 ? 'green' : ($score >= 60 ? 'blue' : ($score >= 40 ? 'yellow' : 'red'));
        $offset = 283 * (1 - $score / 100);
      @endphp
      <div class="panel" style="padding:16px;display:flex;flex-direction:column;align-items:center;gap:10px">
        <p class="section-title" style="margin:0">{{ $label }}</p>
        <div class="score-ring-wrap">
          <svg viewBox="0 0 100 100" class="score-ring">
            <circle class="ring-bg" cx="50" cy="50" r="45"/>
            <circle class="ring-fill {{ $clr }}" cx="50" cy="50" r="45"
              style="stroke-dashoffset: {{ $offset }}"/>
          </svg>
          <div class="ring-center">
            <span class="ring-pct">{{ $score }}</span>
            <span class="ring-label" style="color:var(--{{ $clr === 'blue' ? 'blue' : $clr }})">
              {{ $score >= 80 ? 'GREAT' : ($score >= 60 ? 'GOOD' : ($score >= 40 ? 'FAIR' : 'POOR')) }}
            </span>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">

      <!-- Strengths -->
      <div class="panel" style="padding:16px">
        <p class="section-title">✅ 良い点</p>
        @foreach($data['strengths'] ?? [] as $s)
        <div class="check-item"><span class="check">✓</span><span>{{ $s }}</span></div>
        @endforeach
      </div>

      <!-- Refactor suggestions -->
      <div class="panel" style="padding:16px">
        <p class="section-title">🔧 リファクタ提案</p>
        @foreach($data['refactor_suggestions'] ?? [] as $r)
        <div class="check-item"><span style="color:var(--cyan)">→</span><span>{{ $r }}</span></div>
        @endforeach
      </div>
    </div>

    <!-- Issues -->
    @if(count($data['issues'] ?? []))
    <div class="panel" style="padding:16px;margin-bottom:16px">
      <p class="section-title">⚠️ 検出された問題</p>
      @foreach($data['issues'] as $issue)
      <div class="issue-card {{ $issue['severity'] }}">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
          <div class="issue-title">{{ $issue['title'] }}</div>
          <span class="badge badge-{{ $issue['severity'] === 'high' ? 'red' : ($issue['severity'] === 'medium' ? 'yellow' : 'blue') }}">
            {{ strtoupper($issue['severity']) }}
          </span>
        </div>
        <div class="issue-desc">{{ $issue['description'] }}</div>
      </div>
      @endforeach
    </div>
    @endif

    <!-- Security + Verdict -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="panel" style="padding:16px">
        <p class="section-title">🔒 セキュリティ</p>
        @foreach($data['security_notes'] ?? [] as $note)
        <div class="check-item"><span class="warn">⚠</span><span>{{ $note }}</span></div>
        @endforeach
      </div>
      <div class="panel" style="padding:16px;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center">
        <p class="section-title">🎯 総評</p>
        <p style="font-size:0.85rem;line-height:1.6;color:var(--text-dim)">
          "{{ $data['one_line_verdict'] ?? '' }}"
        </p>
        <div style="margin-top:16px">
          <a href="https://twitter.com/intent/tweet?text={{ urlencode($review->owner.'/'.$review->repo.' のコードレビュー完了！品質スコア: '.$review->quality_score.'/100 #CodeLensAI') }}&url={{ urlencode(request()->url()) }}"
            target="_blank" class="btn btn-primary" style="font-size:0.72rem;padding:8px 14px">
            𝕏 シェアする
          </a>
        </div>
      </div>
    </div>
    @endif
  </div>

</div>

<script>
(function() {
  const status = document.querySelector('meta[name="review-status"]').content;
  if (status === 'complete') return;

  const reviewId = document.querySelector('meta[name="review-id"]').content;
  const lines = document.querySelectorAll('.console-line');
  const progressBar = document.getElementById('progress-bar');
  let lineIdx = 0;

  // Animate console lines
  function showNextLine() {
    if (lineIdx < lines.length) {
      lines[lineIdx].style.opacity = '1';
      lineIdx++;
    }
  }
  const lineTimer = setInterval(showNextLine, 1800);

  // Animate progress bar
  let progress = 0;
  const progressTimer = setInterval(() => {
    progress = Math.min(progress + Math.random() * 8, 88);
    progressBar.style.width = progress + '%';
  }, 800);

  // Start processing
  fetch('/reviews/' + reviewId + '/process', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
  })
  .then(r => r.json())
  .then(data => {
    clearInterval(lineTimer);
    clearInterval(progressTimer);
    progressBar.style.width = '100%';

    if (data.status === 'complete') {
      setTimeout(() => location.reload(), 600);
    } else {
      document.getElementById('process-title').textContent = 'エラーが発生しました';
      document.getElementById('process-step').textContent = data.error || '処理に失敗しました。再度お試しください。';
      document.getElementById('process-icon').textContent = '❌';
    }
  })
  .catch(() => {
    clearInterval(lineTimer);
    clearInterval(progressTimer);
    document.getElementById('process-title').textContent = '接続エラー';
    document.getElementById('process-step').textContent = 'サーバーへの接続に失敗しました。';
    document.getElementById('process-icon').textContent = '❌';
  });
})();
</script>
@endsection
