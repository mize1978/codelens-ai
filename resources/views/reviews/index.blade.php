@extends('layouts.app')
@section('title', 'CodeLens AI — GitHubリポジトリをAIレビュー')

@section('content')
<div style="max-width:860px;margin:0 auto;padding:40px 20px">

  <!-- Hero -->
  <div style="text-align:center;margin-bottom:40px">
    <p style="font-size:0.7rem;letter-spacing:0.25em;color:var(--text-mute);margin-bottom:12px">AI CODE REVIEW SYSTEM v1.0</p>
    <h1 style="font-size:2.4rem;font-weight:900;background:linear-gradient(135deg,var(--cyan),var(--blue),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:12px">
      CodeLens AI
    </h1>
    <p style="font-size:0.9rem;color:var(--text-dim)">GitHubリポジトリURLを入力するだけで、AIがコードを解析してレビューします</p>
  </div>

  <!-- Input form -->
  <div class="panel" style="margin-bottom:32px">
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
      <form action="{{ route('reviews.store') }}" method="POST">
        @csrf
        <p style="font-size:0.65rem;color:var(--text-mute);letter-spacing:0.15em;margin-bottom:8px">GITHUB URL</p>
        <div style="display:flex;gap:10px">
          <input type="text" name="github_url" class="input-field"
            placeholder="https://github.com/owner/repo  または  owner/repo"
            value="{{ old('github_url') }}" autocomplete="off" required>
          <button type="submit" class="btn btn-primary" style="white-space:nowrap">
            ⚡ レビュー開始
          </button>
        </div>
        <p style="font-size:0.65rem;color:var(--text-mute);margin-top:8px">
          ※ GitHubトークン設定で大きなリポジトリや非公開リポジトリにも対応可
        </p>
      </form>
    </div>
  </div>

  <!-- Features -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:32px">
    @foreach([
      ['⚡','高速解析','ファイルツリーを自動解析し、重要ファイルを選択してレビュー'],
      ['🔒','セキュリティ','脆弱性・セキュリティリスクをAIが検出して警告'],
      ['🔧','リファクタ','具体的な改善提案とコード品質スコアを提示'],
    ] as [$icon, $title, $desc])
    <div class="panel" style="padding:16px;text-align:center">
      <div style="font-size:1.6rem;margin-bottom:8px">{{ $icon }}</div>
      <div style="font-size:0.78rem;font-weight:700;margin-bottom:4px">{{ $title }}</div>
      <div style="font-size:0.68rem;color:var(--text-dim);line-height:1.5">{{ $desc }}</div>
    </div>
    @endforeach
  </div>

  <!-- Popular reviews -->
  @if($popular->count())
  <div>
    <p class="section-title">人気のレビュー</p>
    <div style="display:flex;flex-direction:column;gap:8px">
      @foreach($popular as $review)
      <a href="{{ route('reviews.show', $review) }}" style="display:flex;align-items:center;gap:14px;padding:12px 16px;background:var(--bg3);border:1px solid var(--border);border-radius:8px;transition:border-color 0.2s" onmouseover="this.style.borderColor='var(--cyan)'" onmouseout="this.style.borderColor='var(--border)'">
        <span style="font-size:1rem">📦</span>
        <span style="flex:1;font-size:0.82rem">{{ $review->owner }}/{{ $review->repo }}</span>
        @php
          $color = $review->score_color;
        @endphp
        <span class="badge badge-{{ $color }}">{{ $review->overall_score }}%</span>
        <span style="font-size:0.7rem;color:var(--text-mute)">👁 {{ $review->view_count }}</span>
      </a>
      @endforeach
    </div>
  </div>
  @endif

</div>
@endsection
