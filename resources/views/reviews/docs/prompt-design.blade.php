@extends('layouts.app_preview')
@section('title', 'Prompt Design — CodeLensAI Docs')

@section('content')

<div class="jr-sky"></div>

<div class="doc-wrap">

  <a href="{{ route('docs') }}" class="ref-back">← Docs</a>

  <header class="doc-head">
    <div class="doc-kicker">Reference · 03</div>
    <h1 class="doc-title">Prompt Design</h1>
  </header>

  <div class="doc-rule"></div>

  <div class="ref-body">

    <div class="ref-h2">Review pipeline</div>
    <ol class="ref-steps">
      <li>Read repository</li>
      <li>Select important files</li>
      <li>Build repository context</li>
      <li>Generate review</li>
      <li>Save memory</li>
    </ol>

    <div class="ref-last">Last updated 2026.07.26</div>
  </div>

</div>

@include('reviews.docs._style')

@endsection
