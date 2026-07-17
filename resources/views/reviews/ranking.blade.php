@extends('layouts.app')
@section('title', 'ランキング — CodeLens AI')

@section('content')
<div style="max-width:720px;margin:40px auto;padding:0 20px">
  <div style="margin-bottom:24px">
    <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:4px">🏆 レビューランキング</h1>
    <p style="font-size:0.78rem;color:var(--text-dim)">閲覧数の多いレビュー</p>
  </div>

  @if($reviews->count())
  <div style="display:flex;flex-direction:column;gap:8px">
    @foreach($reviews as $i => $review)
    @php $color = $review->score_color; @endphp
    <a href="{{ route('reviews.show', $review) }}"
      style="display:flex;align-items:center;gap:14px;padding:12px 16px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;transition:border-color 0.2s"
      onmouseover="this.style.borderColor='var(--cyan)'" onmouseout="this.style.borderColor='var(--border)'">
      <span style="font-size:1rem;font-weight:700;min-width:28px;color:{{ $i < 3 ? 'var(--yellow)' : 'var(--text-mute)' }}">#{{ $i + 1 }}</span>
      <span style="flex:1;font-size:0.82rem">{{ $review->owner }}/{{ $review->repo }}</span>
      @if($review->language)
        <span class="badge badge-blue" style="font-size:0.6rem">{{ $review->language }}</span>
      @endif
      <span class="badge badge-{{ $color }}">{{ $review->overall_score }}%</span>
      <span style="font-size:0.7rem;color:var(--text-mute)">👁 {{ $review->view_count }}</span>
    </a>
    @endforeach
  </div>
  @else
    <p style="color:var(--text-mute);font-size:0.8rem">まだレビューがありません。</p>
  @endif

  <div style="margin-top:24px">
    <a href="{{ route('reviews.index') }}" class="btn btn-primary">⚡ レビューを始める</a>
  </div>
</div>
@endsection
