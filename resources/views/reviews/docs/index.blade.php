@extends('layouts.app_preview')
@section('title', 'Documentation — CodeLensAI')

@section('content')

<div class="jr-sky"></div>

<div class="doc-wrap">
  <a href="{{ route('reviews.index') }}" class="ref-back">← Workspace</a>

  <header class="doc-head">
    <p class="doc-kicker">Engineering Reference</p>
    <h1 class="doc-title">Documentation</h1>
    <p class="doc-lead">CodeLensAIの仕組みを、必要なときに読むためのリファレンス。</p>
    <p class="doc-sub">設計思想ではなく、実装の話。</p>
  </header>

  <div class="doc-rule"></div>

  <nav class="doc-index">
    <a href="{{ route('docs.page', 'architecture') }}" class="doc-item">
      <span class="di-h">Architecture</span><span class="di-sub">システム全体の構成</span></a>
    <a href="{{ route('docs.page', 'repository-memory') }}" class="doc-item">
      <span class="di-h">Repository Memory</span><span class="di-sub">レビューが蓄積される仕組み</span></a>
    <a href="{{ route('docs.page', 'prompt-design') }}" class="doc-item">
      <span class="di-h">Prompt Design</span><span class="di-sub">レビュー生成の設計</span></a>
    <a href="{{ route('docs.page', 'scoring') }}" class="doc-item">
      <span class="di-h">Scoring</span><span class="di-sub">評価ロジック</span></a>
    <a href="{{ route('docs.page', 'api') }}" class="doc-item">
      <span class="di-h">API</span><span class="di-sub">エンドポイント一覧</span></a>
  </nav>

  <div class="doc-foot">Reference shelf · Architecture / Repository Memory / Prompt Design / Scoring / API</div>

</div>

@include('reviews.docs._style')

@endsection
