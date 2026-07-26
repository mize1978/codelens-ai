@extends('layouts.app_preview')
@section('title', 'API — CodeLensAI Docs')

@section('content')

<div class="jr-sky"></div>

<div class="doc-wrap">

  <a href="{{ route('docs') }}" class="ref-back">← Docs</a>

  <header class="doc-head">
    <div class="doc-kicker">Reference · 05</div>
    <h1 class="doc-title">API</h1>
  </header>

  <div class="doc-rule"></div>

  <div class="ref-body">

    <div class="ref-api">
      <div class="ep"><span class="m post">POST</span><span class="path">/reviews</span></div>
      <div class="ep"><span class="m get">GET</span><span class="path">/reviews</span></div>
      <div class="ep"><span class="m get">GET</span><span class="path">/reviews/{id}</span></div>
      <div class="ep"><span class="m post">POST</span><span class="path">/analyze</span></div>
    </div>

    <div class="ref-last">Last updated 2026.07.26</div>
  </div>

</div>

@include('reviews.docs._style')

@endsection
