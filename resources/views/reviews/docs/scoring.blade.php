@extends('layouts.app_preview')
@section('title', 'Scoring — CodeLensAI Docs')

@section('content')

<div class="jr-sky"></div>

<div class="doc-wrap">

  <a href="{{ route('docs') }}" class="ref-back">← Docs</a>

  <header class="doc-head">
    <div class="doc-kicker">Reference · 04</div>
    <h1 class="doc-title">Scoring</h1>
  </header>

  <div class="doc-rule"></div>

  <div class="ref-body">

    <div class="ref-score">
      <div class="sc-inputs">
        <span class="sc-chip">Quality</span>
        <span class="sc-chip">Security</span>
        <span class="sc-chip">Maintainability</span>
      </div>
      <div class="sc-arrow">↓</div>
      <span class="sc-out">Overall Score</span>
    </div>

    <div class="ref-last">Last updated 2026.07.26</div>
  </div>

</div>

@include('reviews.docs._style')

@endsection
