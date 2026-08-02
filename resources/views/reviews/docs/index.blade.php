@extends('layouts.app_preview')
@section('title', 'Documentation — CodeLensAI')

@section('content')

<div class="ed-sky"></div>

<div class="ed-wrap">
  <a href="{{ route('reviews.index') }}" class="ed-back">← Workspace</a>

  {{-- 探しに来る部屋：設計思想ではなく実装仕様 --}}
  <header class="ed-head">
    <p class="ed-eyebrow">Engineering Reference</p>
    <h1 class="ed-title">Documentation</h1>
    <p class="ed-lead">How CodeLensAI works.</p>
  </header>

  {{-- 一呼吸：Docs は静かに。思想ではなく仕組み --}}
  <div class="ed-verse">
    <p class="jp">設計思想ではなく、<br>実装の話。</p>
    <p class="accent">How it actually works.</p>
  </div>

  <div class="ed-rail"><span>Reference</span></div>

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

  {{-- Docs だけは締め（余韻）を書かない。代わりの一行だけ --}}
  <div class="ed-foot">Documentation is never finished.<br>Last updated 2026.07.26</div>

</div>

@include('reviews._editorial')
@include('reviews.docs._style')

@endsection
