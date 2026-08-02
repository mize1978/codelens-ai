@extends('layouts.app_preview')
@section('title', 'Articles — CodeLensAI Engineering Journal')

@section('content')

<div class="ed-sky"></div>

<div class="ed-wrap">
  <a href="{{ route('reviews.index') }}" class="ed-back">← Workspace</a>

  {{-- 部屋の扉：ここは「更新され続ける思想の場所」。ブログではない --}}
  <header class="ed-head">
    <p class="ed-eyebrow">Engineering Journal</p>
    <h1 class="ed-title">Articles</h1>
    <p class="ed-lead">The ideas behind CodeLensAI.</p>
  </header>

  {{-- 一呼吸：機能ではなく、なぜそう作ったか --}}
  <div class="ed-verse">
    <p class="jp">機能の話ではなく、<br>なぜそう作ったか、の話。</p>
    <p class="accent">Ideas, before pixels.</p>
  </div>

  <div class="ed-rail"><span>Writings</span></div>

  {{-- 思想の目次：時系列でなく「思想 → AI → UI → 細部」の順に読む --}}
  <nav class="ed-list">

    <a href="{{ route('articles.experience-first') }}" class="ed-row">
      <span class="ed-tag">Featured · Manifesto</span>
      <span class="ed-row-h">Experience First</span>
      <span class="ed-row-sub">レビュー画面ではなく、一冊のレポートを設計した理由</span>
    </a>

    <a href="{{ route('articles.repository-intelligence') }}" class="ed-row">
      <span class="ed-row-h">Repository Intelligence</span>
      <span class="ed-row-sub">AI は何を読んで、何を覚えるのか</span>
    </a>

    <a href="{{ route('articles.reading-code') }}" class="ed-row">
      <span class="ed-row-h">Reading Code</span>
      <span class="ed-row-sub">コードレビューを「採点」ではなく「読書体験」に変えた設計</span>
    </a>

    <a href="{{ route('articles.information-flow') }}" class="ed-row">
      <span class="ed-row-h">Information Flow</span>
      <span class="ed-row-sub">情報を並べるのではなく、読む順番を設計する</span>
    </a>

  </nav>

  <div class="ed-foot">More articles — coming soon.</div>

</div>

@include('reviews._editorial')

@endsection
