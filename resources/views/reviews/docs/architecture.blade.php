@extends('layouts.app_preview')
@section('title', 'Architecture — CodeLensAI Docs')

@section('content')

<div class="jr-sky"></div>

<div class="doc-wrap">

  <a href="{{ route('docs') }}" class="ref-back">← Docs</a>

  <header class="doc-head">
    <div class="doc-kicker">Reference · 01</div>
    <h1 class="doc-title">Architecture</h1>
  </header>

  <div class="doc-rule"></div>

  <div class="ref-body">
    <p class="ref-oneliner">CodeLensAI is built around one flow.</p>

    <div class="ref-flow">
      <span class="fl-step">Read</span>
      <span class="fl-line"></span>
      <span class="fl-step">Understand</span>
      <span class="fl-line"></span>
      <span class="fl-step">Remember</span>
      <span class="fl-line"></span>
      <span class="fl-step">Review</span>
    </div>

    <div class="ref-last">Last updated 2026.07.26</div>
  </div>

</div>

@include('reviews.docs._style')

@endsection
