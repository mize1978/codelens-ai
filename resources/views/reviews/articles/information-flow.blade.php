@extends('layouts.app_preview')
@section('title', 'Information Flow — CodeLensAI')

@section('content')

{{-- 🌌 同じ建物の中：世界(#81) を記事の背後にも薄く残す --}}
<div class="jr-sky"></div>

<article class="art-wrap">

  <a href="{{ route('articles') }}" class="art-back">← Articles</a>

  <header class="art-head">
    <div class="art-kicker">Essay · 04</div>
    <h1 class="art-title">Information Flow</h1>
    <p class="art-sub">情報を並べるのではなく、読む順番を設計する</p>
    <div class="art-meta">CodeLensAI · Engineering Journal</div>
  </header>

  <div class="art-rule"></div>

  <div class="art-body">

    <p class="art-lead">情報は、全部見せた方が親切だ。</p>

    <p>——そう、思われている。<br>
       画面は、情報量が多いほど良い UI だと。</p>

    <h2>人は、全部を見ない</h2>
    <p>でも実際には、人は画面のすべてを読まない。<br>
       最初の数秒で、「何を見るか」「何を飛ばすか」「何を後で読むか」を決めている。</p>
    <p>親切のつもりで全部並べた情報は、たいてい「全部、後で」に分類される。</p>

    <h2>設計していたのは、順番だった</h2>
    <p>だから CodeLensAI が本当に設計していたのは、情報そのものではなかった。<br>
       それを<strong>読む順番</strong>だった。</p>
    <p>このシリーズで何度も出てきた小さな数字は、CSS の話ではない。<em>認知</em>の話だ。</p>

    <div class="art-cognition">
      <div class="cog"><span class="cog-n">0.5<i>s</i></span><span class="cog-t">スコアが出てから本編に入るまでの間。「待たせた」時間ではない。<b>「章が変わった」と脳に伝える区切り</b>だ。</span></div>
      <div class="cog"><span class="cog-n">6<i>px</i></span><span class="cog-t">数字とバッジの距離。「詰めた余白」ではない。<b>二つを一つの情報として認識させる距離</b>だ。</span></div>
      <div class="cog"><span class="cog-n">8<i>px</i></span><span class="cog-t">一文を二つに割る間。「改行」ではない。<b>要点→説明という読む順番をつくる息継ぎ</b>だ。</span></div>
    </div>

    <blockquote>人は情報を読むのではない。<br>順番を読む。</blockquote>

    <h2>理解は、設計できる</h2>
    <p>情報量を増やしても、理解は増えない。<br>
       何を先に見せ、何を後に回すか——それが、理解の速度を決める。</p>
    <p>つまり、理解は偶然ではない。<strong>設計できる</strong>。</p>

  </div>

  <div class="art-close">
    <div class="art-close-mark">Information Flow.</div>
    <p>情報は、並べるものではない。<br>理解の順番を、設計するものだ。</p>
  </div>

  {{-- 思想書の最終章：4本を一望させて「一冊」を閉じる --}}
  <div class="art-series">
    <div class="art-series-head">The four essays. One idea.</div>
    <a href="{{ route('articles.experience-first') }}" class="art-series-item">
      <b>Experience First</b><span>私たちは何を作ったのか。</span></a>
    <a href="{{ route('articles.repository-intelligence') }}" class="art-series-item">
      <b>Repository Intelligence</b><span>AI はどう考えるのか。</span></a>
    <a href="{{ route('articles.reading-code') }}" class="art-series-item">
      <b>Reading Code</b><span>良いレビューとは何か。</span></a>
    <span class="art-series-item is-current">
      <b>Information Flow</b><span>理解はどう生まれるのか。</span></span>
  </div>

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

  .art-body blockquote{ margin:34px 0; padding:6px 0 6px 22px; border-left:2px solid #38bdf8;
    font-size:18px; line-height:1.8; color:#e8edf5; font-weight:500; }

  {{-- 数字を「認知」として語るブロック：このシリーズで何度も出た 0.5s/6px/8px の正体 --}}
  .art-cognition{ margin:30px 0 8px; border-top:1px solid rgba(255,255,255,.07); }
  .cog{ display:flex; gap:20px; align-items:baseline; padding:20px 4px; border-bottom:1px solid rgba(255,255,255,.07); }
  .cog-n{ flex:0 0 66px; font-family:'JetBrains Mono',monospace; font-size:22px; font-weight:700; color:#38bdf8; letter-spacing:-.5px; }
  .cog-n i{ font-style:normal; font-size:13px; color:#6b7488; margin-left:2px; }
  .cog-t{ font-size:15px; line-height:1.8; color:#c2ccdb; }
  .cog-t b{ color:#f0f3f8; font-weight:600; }

  .art-close{ margin-top:52px; padding-top:38px; border-top:1px solid rgba(255,255,255,.09); text-align:center; }
  .art-close-mark{ font-size:25px; font-weight:700; color:#f0f3f8; letter-spacing:-.3px; margin-bottom:16px; }
  .art-close p{ font-size:16px; line-height:1.95; color:#9aa4b8; margin:0; }

  {{-- 思想書を閉じる：4本の一望 --}}
  .art-series{ margin-top:60px; padding:26px 22px; border:1px solid rgba(255,255,255,.08); border-radius:12px; background:rgba(255,255,255,.015); }
  .art-series-head{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:2.5px; text-transform:uppercase; color:#6b7488; margin-bottom:18px; text-align:center; }
  .art-series-item{ display:flex; justify-content:space-between; align-items:baseline; gap:16px; padding:13px 6px;
    border-top:1px solid rgba(255,255,255,.06); text-decoration:none; transition:padding-left .2s ease; }
  .art-series-item:hover{ padding-left:12px; }
  .art-series-item b{ font-size:15px; font-weight:600; color:#c2ccdb; }
  .art-series-item:hover b{ color:#fff; }
  .art-series-item span{ font-size:12.5px; color:#8792a6; text-align:right; }
  .art-series-item.is-current b{ color:#38bdf8; }
  .art-series-item.is-current{ cursor:default; }
  .art-series-item.is-current:hover{ padding-left:6px; }
</style>

@endsection
