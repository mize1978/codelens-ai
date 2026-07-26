@extends('layouts.app_preview')
@section('title', 'Experience First — CodeLensAI')

@section('content')

{{-- 🌌 同じ建物の中：世界(#81) を記事の背後にも薄く残す --}}
<div class="jr-sky"></div>

<article class="art-wrap">

  <a href="{{ route('articles') }}" class="art-back">← Articles</a>

  <header class="art-head">
    <div class="art-kicker">Manifesto · 01</div>
    <h1 class="art-title">Experience First</h1>
    <p class="art-sub">レビュー画面ではなく、一冊のレポートを設計した理由</p>
    <div class="art-meta">CodeLensAI · Engineering Journal</div>
  </header>

  <div class="art-rule"></div>

  <div class="art-body">

    <p class="art-lead">最初は、レビュー画面を作ろうとしていた。</p>

    <p>スコアを出す。Issue を並べる。よくある構成だ。<br>
       でも途中で気付いた。私たちが作っていたのは、画面ではなかった。<strong>一冊のレポート</strong>だった。</p>

    <h2>採点ではなく、レポート</h2>
    <p>コードレビューツールの多くは、点数を出して終わる。62点。B評価。——そこで物語が閉じる。<br>
       けれど本物のレビューは、点数では終わらない。点数は、長いレポートの中の<em>一行</em>にすぎない。</p>
    <p>だから、設計をひっくり返した。<br>
       <span class="art-flow">Analyze → Score</span> ではなく、<span class="art-flow">Analyze → Summary → Report</span>。<br>
       スコアは結論ではなく、読み始めの入口になった。</p>

    <h2>建物として設計する</h2>
    <p>そう決めた瞬間、UI は「画面の集まり」ではなく、一つの<strong>建物</strong>になった。<br>
       ロビーがあり、廊下があり、部屋がある。</p>
    <p>レビューを依頼しても、別画面には飛ばない。同じ部屋の中で世界が少し暗くなり、AI が読み始める。<br>
       Read。Understand。Remember。<br>
       やがてレポートが本編として立ち上がり、最後に Memory として棚に残る。そして静かに、本を閉じる。End of Review。</p>

    <blockquote>ページ遷移は、一度もない。<br>本を読むのに、画面は切り替わらないから。</blockquote>

    <h2>細部が、体験をつくる</h2>
    <p>この体験は、大きな機能ではなく、小さな判断の積み重ねでできている。</p>
    <ul>
      <li>スコアが着地してから本編に入るまでの、<strong>0.5秒の間</strong>。</li>
      <li>数字とバッジを一体に見せる、<strong>6px の距離</strong>。</li>
      <li>一文を二つに分ける、<strong>8px の息継ぎ</strong>。</li>
    </ul>
    <p>どれも数値化できない。コードは一行も増えていない。<br>
       けれどこの距離と余白と間が、「眺める」を「読む」に変える。</p>

  </div>

  <div class="art-close">
    <div class="art-close-mark">Experience First.</div>
    <p>私たちは UI を作ったのではない。<br>読む体験を設計した。</p>
  </div>

  {{-- 「戻る」より「次を読む」：読み終えた人を思想の順路の次へ --}}
  <a href="{{ route('articles.repository-intelligence') }}" class="art-next">
    <span class="art-next-label">Next →</span>
    <span class="art-next-title">Repository Intelligence</span>
    <span class="art-next-sub">AI は何を読んで、何を覚えるのか</span>
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
  {{-- LPのHeroではなく「記事タイトル」：本文との階層を自然にするため約93%へ --}}
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

  .art-body ul{ margin:0 0 24px; padding-left:0; list-style:none; }
  .art-body li{ font-size:16.5px; line-height:1.8; color:#c2ccdb; margin-bottom:12px; padding-left:22px; position:relative; }
  .art-body li::before{ content:'—'; position:absolute; left:0; color:#38bdf8; }

  .art-close{ margin-top:58px; padding-top:38px; border-top:1px solid rgba(255,255,255,.09); text-align:center; }
  .art-close-mark{ font-size:25px; font-weight:700; color:#f0f3f8; letter-spacing:-.3px; margin-bottom:16px; }
  .art-close p{ font-size:16px; line-height:1.95; color:#9aa4b8; margin:0; }

  .art-next{ display:block; text-decoration:none; margin-top:58px; padding:24px 22px; border:1px solid rgba(255,255,255,.08);
    border-radius:12px; background:rgba(255,255,255,.02); transition:border-color .25s ease, transform .25s ease, background .25s ease; }
  .art-next:hover{ border-color:rgba(56,189,248,.4); transform:translateY(-2px); background:rgba(56,189,248,.04); }
  .art-next-label{ display:block; font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:2px; text-transform:uppercase; color:#38bdf8; margin-bottom:9px; }
  .art-next-title{ display:block; font-size:19px; font-weight:600; color:#e8edf5; margin-bottom:5px; }
  .art-next-sub{ display:block; font-size:13px; color:#9aa4b8; line-height:1.55; }
</style>

@endsection
