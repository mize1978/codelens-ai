@extends('layouts.app_preview')
@section('title', 'Repository Memory — CodeLensAI Docs')

@section('content')

<div class="jr-sky"></div>

<div class="doc-wrap">

  <a href="{{ route('docs') }}" class="ref-back">← Docs</a>

  <header class="doc-head">
    <div class="doc-kicker">Reference · 02</div>
    <h1 class="doc-title">Repository Memory</h1>
  </header>

  <div class="doc-rule"></div>

  <div class="ref-body">

    <div class="ref-h2">Purpose</div>
    <p class="ref-fact">Store previous reviews.</p>

    <div class="ref-h2">Memory stores</div>
    <ul class="ref-list">
      <li>repository</li>
      <li>summary</li>
      <li>score</li>
      <li>important files</li>
      <li>review history</li>
    </ul>

    <div class="ref-h2">Behavior</div>
    <p class="ref-fact">Every completed review creates one memory record.</p>

    <div class="ref-last">Last updated 2026.07.26</div>
  </div>

</div>

@include('reviews.docs._style')

@endsection
