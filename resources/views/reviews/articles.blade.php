@extends('layouts.app_preview')
@section('title', 'Articles — CodeLensAI Engineering Journal')

@section('content')

{{-- 🌌 世界(#81) を Journal の背後にも薄く残す（同じ建物の中） --}}
<div class="jr-sky"></div>

<div class="journal-wrap">

  {{-- 部屋の扉：ここは「更新され続ける思想の場所」。ブログではない --}}
  <header class="jr-head">
    <div class="jr-kicker">Engineering Journal</div>
    <h1 class="jr-title">Articles</h1>
    <p class="jr-lead">The ideas behind CodeLensAI.</p>
  </header>

  <div class="jr-rule"></div>

  {{-- 思想の目次：時系列でなく「思想 → AI → UI → 細部」の順に読む --}}
  <nav class="jr-index">

    <a href="{{ route('articles.experience-first') }}" class="jr-item jr-featured">
      <span class="jr-tag">Featured · Manifesto</span>
      <span class="jr-h">Experience First</span>
      <span class="jr-sub">レビュー画面ではなく、一冊のレポートを設計した理由</span>
    </a>

    <a href="{{ route('articles.repository-intelligence') }}" class="jr-item">
      <span class="jr-h">Repository Intelligence</span>
      <span class="jr-sub">AI は何を読んで、何を覚えるのか</span>
    </a>

    <a href="{{ route('articles.reading-code') }}" class="jr-item">
      <span class="jr-h">Reading Code</span>
      <span class="jr-sub">コードレビューを「採点」ではなく「読書体験」に変えた設計</span>
    </a>

    <a href="{{ route('articles.information-flow') }}" class="jr-item">
      <span class="jr-h">Information Flow</span>
      <span class="jr-sub">情報を並べるのではなく、読む順番を設計する</span>
    </a>

  </nav>

  <div class="jr-rule"></div>

  <span class="jr-soon">More articles — coming soon</span>

</div>

<style>
  .jr-sky{ position:fixed; inset:0; z-index:-1;
    background:
      radial-gradient(1100px 620px at 50% -8%, rgba(56,120,190,.14), transparent 60%),
      radial-gradient(900px 520px at 85% 12%, rgba(120,80,190,.10), transparent 62%); }

  .journal-wrap{ max-width:720px; margin:0 auto; padding:66px 24px 96px; }

  .jr-head{ margin-bottom:26px; }
  .jr-kicker{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:3px; text-transform:uppercase; color:#6b7488; margin-bottom:14px; }
  .jr-title{ font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif; font-size:clamp(30px,5vw,40px); font-weight:700; color:#e8edf5; margin:0 0 12px; letter-spacing:-.5px; }
  .jr-lead{ font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif; font-size:15px; color:#8792a6; margin:0; letter-spacing:.2px; }

  .jr-rule{ height:1px; background:rgba(255,255,255,.09); margin:0; }

  .jr-index{ display:flex; flex-direction:column; }
  .jr-item{ position:relative; display:block; text-decoration:none; padding:26px 34px 26px 6px;
    border-bottom:1px solid rgba(255,255,255,.06); transition:transform .25s ease; }
  .jr-item:last-child{ border-bottom:none; }
  {{-- ① クリックできるのが伝わるホバー：→ がスッと現れ、タイトルだけ明るく --}}
  .jr-item::after{ content:'→'; position:absolute; right:8px; top:29px; font-family:'JetBrains Mono',monospace;
    font-size:14px; color:#38bdf8; opacity:0; transform:translateX(-6px); transition:opacity .25s ease, transform .25s ease; }
  .jr-item:hover{ transform:translateX(3px); }
  .jr-item:hover::after{ opacity:1; transform:translateX(0); }
  {{-- ② 1本目だけ代表作（Manifesto）感 --}}
  .jr-tag{ display:block; font-family:'JetBrains Mono',monospace; font-size:10px; letter-spacing:2px;
    text-transform:uppercase; color:#38bdf8; margin-bottom:9px; }
  .jr-h{ display:block; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif;
    font-size:21px; font-weight:600; color:#e8edf5; margin-bottom:7px; transition:color .2s ease; }
  .jr-featured .jr-h{ font-size:23px; }
  .jr-item:hover .jr-h{ color:#fff; }
  .jr-sub{ display:block; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif;
    font-size:13.5px; line-height:1.65; color:#9aa4b8; }

  {{-- ③ 未実装は正直に：クリックを誘わない静かな注記 --}}
  .jr-soon{ display:inline-block; margin-top:26px; font-family:'JetBrains Mono',monospace; font-size:12px;
    letter-spacing:1px; text-transform:uppercase; color:#4d5568; cursor:default; }
</style>

@endsection
