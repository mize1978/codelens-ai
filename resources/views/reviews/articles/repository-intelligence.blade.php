@extends('layouts.app_preview')
@section('title', 'Repository Intelligence — CodeLensAI')

@section('content')

{{-- 🌌 同じ建物の中：世界(#81) を記事の背後にも薄く残す --}}
<div class="jr-sky"></div>

<article class="art-wrap">

  <a href="{{ route('articles') }}" class="art-back">← Articles</a>

  <header class="art-head">
    <div class="art-kicker">Essay · 02</div>
    <h1 class="art-title">Repository Intelligence</h1>
    <p class="art-sub">AI は何を読んで、何を覚えるのか</p>
    <div class="art-meta">CodeLensAI · Engineering Journal</div>
  </header>

  <div class="art-rule"></div>

  <div class="art-body">

    <p class="art-lead">リポジトリには、何百ものファイルがある。</p>

    <p>その全部を、同じ重さで読む。<br>
       それは丁寧に見えて、実は<strong>何も読んでいない</strong>のと同じだ。</p>

    <h2>読む場所を、AI に選ばせる</h2>
    <p>人間のレビュアーは、最初から全ファイルを開いたりしない。<br>
       設計の中心から読む。モデル、エントリポイント、要のロジック——そこに構造が現れるからだ。</p>
    <p>CodeLensAI も同じにした。全ファイルを機械的に舐めるのではなく、AI に「<em>どこを読むべきか</em>」をまず選ばせる。</p>
    <blockquote>レビューの質は、何を書くかより先に、<br>どこを読むかで決まっている。</blockquote>
    <p>重要ファイルの選定こそが、最初の——そして最大の——判断だ。</p>

    <h2>読むだけでは、忘れる</h2>
    <p>多くのツールは、レビューのたびにゼロから読む。昨日の理解は、今日には残っていない。</p>
    <p>だから <strong>Read</strong> の次に <strong>Remember</strong> を置いた。<br>
       レビューは一度きりの採点ではなく、<em>リポジトリの記憶</em>に書き込まれる。</p>
    <p>コードが育つように、理解も育つ。次に戻ってきたとき、CodeLensAI は「前回どう読んだか」から再開できる。</p>

    <h2>Intelligence とは、選ぶこと</h2>
    <p>賢さとは、すべてを処理する力ではない。<br>
       何を見て、何を無視し、何を覚えておくかを<strong>決める</strong>力だ。</p>
    <p><span class="art-flow">Read → Understand → Remember</span><br>
       この三語は画面の演出ではなく、リポジトリを「知性」として扱うための設計そのものだ。</p>

  </div>

  <div class="art-close">
    <div class="art-close-mark">Repository Intelligence.</div>
    <p>Intelligence は、すべてを見る力ではない。<br>どこを読み、何を覚えるかを、選ぶ勇気だ。</p>
  </div>

  {{-- 「戻る」より「次を読む」：思想の順路の次へ --}}
  <a href="{{ route('articles.reading-code') }}" class="art-next">
    <span class="art-next-label">Next →</span>
    <span class="art-next-title">Reading Code</span>
    <span class="art-next-sub">コードレビューを「採点」ではなく「読書体験」に変えた設計</span>
  </a>

</article>

<style>
  .jr-sky{ position:fixed; inset:0; z-index:-1;
    background:
      radial-gradient(1100px 620px at 50% -8%, rgba(56,120,190,.14), transparent 60%),
      radial-gradient(900px 520px at 85% 12%, rgba(120,80,190,.10), transparent 62%); }

  .art-wrap{ max-width:680px; margin:0 auto; padding:48px 24px 96px;
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif; }

  .art-back{ display:inline-block; font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:1px;
    color:#6b7488; text-decoration:none; margin-bottom:44px; transition:color .2s ease; }
  .art-back:hover{ color:#38bdf8; }

  .art-head{ margin-bottom:28px; }
  .art-kicker{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:3px; text-transform:uppercase; color:#38bdf8; margin-bottom:16px; }
  .art-title{ font-size:clamp(29px,5.3vw,42px); font-weight:700; color:#f0f3f8; margin:0 0 14px; letter-spacing:-.5px; line-height:1.16; }
  .art-sub{ font-size:16px; color:#9aa4b8; margin:0 0 18px; line-height:1.6; }
  .art-meta{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:1.5px; text-transform:uppercase; color:#4d5568; }

  .art-rule{ height:1px; background:rgba(255,255,255,.09); margin:0 0 40px; }

  .art-body p{ font-size:16.5px; line-height:1.95; color:#c2ccdb; margin:0 0 24px; }
  .art-lead{ font-size:19px !important; color:#e8edf5 !important; font-weight:500; }
  .art-body strong{ color:#f0f3f8; font-weight:700; }
  .art-body em{ font-style:normal; color:#38bdf8; font-weight:600; }
  .art-body h2{ font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:2px; text-transform:uppercase;
    color:#6b7488; margin:46px 0 18px; }
  .art-flow{ font-family:'JetBrains Mono',monospace; font-size:13.5px; color:#e8edf5;
    background:rgba(255,255,255,.05); padding:2px 9px; border-radius:5px; white-space:nowrap; }

  .art-body blockquote{ margin:34px 0; padding:6px 0 6px 22px; border-left:2px solid #38bdf8;
    font-size:18px; line-height:1.8; color:#e8edf5; font-weight:500; }

  .art-close{ margin-top:58px; padding-top:38px; border-top:1px solid rgba(255,255,255,.09); text-align:center; }
  .art-close-mark{ font-size:25px; font-weight:700; color:#f0f3f8; letter-spacing:-.3px; margin-bottom:16px; }
  .art-close p{ font-size:16px; line-height:1.95; color:#9aa4b8; margin:0; }

  .art-next{ display:block; text-decoration:none; margin-top:58px; padding:24px 22px; border:1px solid rgba(255,255,255,.08);
    border-radius:12px; background:rgba(255,255,255,.02); transition:border-color .25s ease, transform .25s ease, background .25s ease; }
  .art-next:hover{ border-color:rgba(56,189,248,.4); transform:translateY(-2px); background:rgba(56,189,248,.04); }
  .art-next-label{ display:block; font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#38bdf8; margin-bottom:9px; }
  .art-next-title{ display:block; font-size:19px; font-weight:600; color:#e8edf5; margin-bottom:5px; }
  .art-next-sub{ display:block; font-size:13px; color:#9aa4b8; line-height:1.55; }
  {{-- 未実装は正直に：クリックを誘わない --}}
  .art-next-soon{ opacity:.5; cursor:default; }
  .art-next-soon:hover{ border-color:rgba(255,255,255,.08); transform:none; background:rgba(255,255,255,.02); }
  .art-next-soon .art-next-label{ color:#6b7488; }
  .art-next-soon .art-next-sub{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:1px; text-transform:uppercase; color:#4d5568; }
</style>

@endsection
