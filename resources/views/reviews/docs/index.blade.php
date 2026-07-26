@extends('layouts.app_preview')
@section('title', 'Documentation — CodeLensAI')

@section('content')

<div class="jr-sky"></div>

<div class="doc-wrap">

  {{-- 探しに来る部屋：設計思想ではなく実装仕様 --}}
  <header class="doc-head">
    <div class="doc-kicker">Engineering Reference</div>
    <h1 class="doc-title">Documentation</h1>
    <p class="doc-lead">How CodeLensAI works.</p>
    <p class="doc-sub">設計思想ではなく、実装仕様。<br>CodeLensAI を構成する仕組みをまとめています。</p>
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

  <div class="doc-rule"></div>

  {{-- Docs だけは締め（余韻）を書かない。代わりの一行だけ --}}
  <div class="doc-foot">Documentation is never finished.<br>Last updated 2026.07.26</div>

</div>

@include('reviews.docs._style')

@endsection
