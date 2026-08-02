@extends('layouts.app_preview')
@section('title', 'CodeLensAI — StyleBook')

@section('content')
<style>
  .sb-wrap { max-width: 900px; margin: 0 auto; padding: 40px 18px 90px; }
  .sb-head { text-align: center; margin: 8px 0 44px; }
  .sb-eyebrow { font-size: .62rem; font-weight: 700; letter-spacing: .38em; color: var(--cyan); text-transform: uppercase; opacity: .85; }
  .sb-title { font-size: clamp(20px,4vw,30px); font-weight: 700; letter-spacing: -.3px; line-height: 1.25; margin: 14px 0 0;
    background: linear-gradient(135deg,#ffffff,#8fc7ff); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
  .sb-lead { font-size: .82rem; line-height: 1.9; color: rgba(204,232,255,.55); margin: 16px auto 0; max-width: 560px; }
  .sb-verse { max-width: 470px; margin: 40px auto 64px; text-align: center; }
  .sb-verse p { font-size: .9rem; line-height: 2.05; color: rgba(204,232,255,.5); margin: 0 0 20px; }
  .sb-verse p:last-child { margin-bottom: 0; }
  .sb-verse .jp { color: #d5ecff; }
  .sb-verse .accent { color: #c3e5ff; font-style: italic; }

  /* index cards */
  .sb-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin: 0 0 64px; }
  @media (max-width: 640px){ .sb-grid { grid-template-columns: 1fr; } }
  .sb-card { display: block; text-decoration: none; border: 1px solid var(--border); border-radius: 12px;
    background: linear-gradient(180deg, rgba(0,200,255,.03), rgba(0,0,0,0)); padding: 20px 22px;
    transition: border-color .22s, box-shadow .22s, transform .22s; }
  .sb-card:hover { border-color: rgba(0,205,255,.5); box-shadow: 0 0 26px rgba(0,200,255,.10); transform: translateY(-2px); }
  .sb-num { font-size: .6rem; letter-spacing: .2em; color: rgba(0,205,255,.6); font-weight: 700; }
  .sb-name { display: block; font-size: 1rem; font-weight: 700; color: #eaf5ff; margin: 4px 0 8px; letter-spacing: .02em; }
  .sb-tease { display: block; font-size: .78rem; line-height: 1.75; color: rgba(204,232,255,.55); }
  .sb-tease .no { color: var(--red); }
  .sb-tease .yes { color: var(--green); }

  /* chapters */
  .sb-rail { display: flex; align-items: center; margin: 40px 0 26px; }
  .sb-rail::before, .sb-rail::after { content: ''; flex: 1; height: 1px; }
  .sb-rail::before { background: linear-gradient(90deg, transparent, rgba(0,200,255,.22)); }
  .sb-rail::after  { background: linear-gradient(270deg, transparent, rgba(0,200,255,.22)); }
  .sb-rail span { padding: 0 16px; font-size: .58rem; letter-spacing: .35em; color: rgba(255,255,255,.34); text-transform: uppercase; }

  .sb-ch { scroll-margin-top: 84px; padding: 22px 0 26px; border-top: 1px solid rgba(0,200,255,.08); }
  .sb-ch:first-of-type { border-top: none; }
  .sb-ch-h { display: flex; align-items: baseline; gap: 12px; margin-bottom: 14px; }
  .sb-ch-n { font-size: .62rem; letter-spacing: .2em; color: rgba(0,205,255,.55); font-weight: 700; }
  .sb-ch-t { font-size: 1.15rem; font-weight: 700; color: #eaf5ff; letter-spacing: .01em; }
  .sb-ch p { font-size: .82rem; line-height: 1.9; color: rgba(204,232,255,.66); margin: 0 0 6px; }
  .sb-ch b { color: #eaf5ff; font-weight: 600; }
  .sb-ch ul { list-style: none; margin: 6px 0; padding: 0; display: flex; flex-direction: column; gap: 7px; }
  .sb-ch li { position: relative; padding-left: 15px; font-size: .8rem; line-height: 1.7; color: rgba(204,232,255,.6); }
  .sb-ch li::before { content: '—'; position: absolute; left: 0; color: rgba(0,205,255,.5); }
  .sb-big { font-size: 1.05rem; line-height: 1.7; color: #dff0ff; font-weight: 600; letter-spacing: .01em; margin: 4px 0 10px; }
  .sb-vpair { display: grid; grid-template-columns: 1fr; gap: 5px; margin: 4px 0; }
  .sb-vrow { font-size: .82rem; line-height: 1.7; }
  .sb-vrow .no { color: var(--red); } .sb-vrow .yes { color: var(--green); font-weight: 600; }
  .sb-soon { font-size: .72rem; color: rgba(255,204,0,.6); letter-spacing: .04em; border: 1px dashed rgba(255,204,0,.22); border-radius: 8px; padding: 8px 12px; display: inline-block; }
  .sb-top { display: inline-block; margin-top: 10px; font-size: .64rem; letter-spacing: .12em; color: rgba(0,205,255,.55); text-decoration: none; text-transform: uppercase; }
  .sb-top:hover { color: var(--cyan); }
  .sb-back { display: inline-block; margin-bottom: 18px; font-size: .66rem; letter-spacing: .14em; text-transform: uppercase; color: rgba(0,205,255,.6); text-decoration: none; }
  .sb-back:hover { color: var(--cyan); }
  @media (prefers-reduced-motion: reduce){
    html { scroll-behavior: auto; }
    .sb-card, .sb-card:hover { transition: none; transform: none; }
  }
</style>

<div class="sb-wrap" id="sbTop">
  <a href="{{ route('reviews.index') }}" class="sb-back">← Workspace</a>
  <header class="sb-head">
    <p class="sb-eyebrow">Stylebook</p>
    <h1 class="sb-title">How CodeLensAI expresses what it believes.</h1>
    <p class="sb-lead">CodeLensAI is built on principles before pixels.<br>Every color, motion, sentence, and interaction exists for a reason.</p>
  </header>

  <div class="sb-verse">
    <p class="jp">すべてのインターフェースには、<br>そのプロダクトらしい「話し方」がある。</p>
    <p>Some products explain themselves.<br>Some products prove themselves.</p>
    <p class="accent">CodeLensAI tries to remember.</p>
  </div>

  {{-- ── index (目次) ── --}}
  <div class="sb-grid">
    <a class="sb-card" href="#ch01"><span class="sb-num">01</span><span class="sb-name">Brand</span><span class="sb-tease">何を信じているか。</span></a>
    <a class="sb-card" href="#ch02"><span class="sb-num">02</span><span class="sb-name">Visual Identity</span><span class="sb-tease">どう見分けられるか。</span></a>
    <a class="sb-card" href="#ch03"><span class="sb-num">03</span><span class="sb-name">Color System</span><span class="sb-tease">どんな光の中にいるか。</span></a>
    <a class="sb-card" href="#ch04"><span class="sb-num">04</span><span class="sb-name">Typography</span><span class="sb-tease">どう読ませるか。</span></a>
    <a class="sb-card" href="#ch05"><span class="sb-num">05</span><span class="sb-name">Voice</span><span class="sb-tease">CodeLensAI は、どう話すか。</span></a>
    <a class="sb-card" href="#ch06"><span class="sb-num">06</span><span class="sb-name">Motion</span><span class="sb-tease">なぜ、その動きなのか。</span></a>
    <a class="sb-card" href="#ch07"><span class="sb-num">07</span><span class="sb-name">Character</span><span class="sb-tease">CodeLensくんの出演ルール。</span></a>
    <a class="sb-card" href="#ch08"><span class="sb-num">08</span><span class="sb-name">Components</span><span class="sb-tease">どんな部屋でできているか。</span></a>
    <a class="sb-card" href="#ch09"><span class="sb-num">09</span><span class="sb-name">Artwork</span><span class="sb-tease">どう描かれるか。</span></a>
    <a class="sb-card" href="#ch10"><span class="sb-num">10</span><span class="sb-name">Principles in Practice</span><span class="sb-tease">思想から、実装まで。</span></a>
    <a class="sb-card" href="#ch11"><span class="sb-num">11</span><span class="sb-name">Future</span><span class="sb-tease">これから、どこへ。</span></a>
  </div>

  <div class="sb-rail"><span>Full Chapters</span></div>

  {{-- ── chapters (全文) ── --}}
  <section class="sb-ch" id="ch01">
    <div class="sb-ch-h"><span class="sb-ch-n">01</span><span class="sb-ch-t">Brand</span></div>
    <p class="sb-big">Your repository remembers. → Repository Intelligence, built on memory.</p>
    <ul>
      <li><b>Repository Intelligence</b> — a place where software can be read.</li>
      <li><b>Keep the proof. Update the promise.</b> — Proof は変えない／Promise は進化させる。</li>
      <li><b>Brand pillars</b> — Read → Understand → Remember → Grow（Understand を省かない）</li>
      <li><b>built on memory</b>（powered by ではない：powered by=動力／built on=設計思想）</li>
    </ul>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch02">
    <div class="sb-ch-h"><span class="sb-ch-n">02</span><span class="sb-ch-t">Visual Identity</span></div>
    <p>Logo / Symbol / Clear space / Do · Don't。</p>
    <p class="sb-soon">Living document · Being refined.</p>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch03">
    <div class="sb-ch-h"><span class="sb-ch-n">03</span><span class="sb-ch-t">Color System</span></div>
    <p>Primary / Accent / Semantic colors / Backgrounds。</p>
    <p class="sb-soon">Living document · Being refined.</p>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch04">
    <div class="sb-ch-h"><span class="sb-ch-n">04</span><span class="sb-ch-t">Typography</span></div>
    <p>Font stack / Hierarchy / Letter spacing / Numbers。</p>
    <p class="sb-soon">Living document · Being refined.</p>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch05">
    <div class="sb-ch-h"><span class="sb-ch-n">05</span><span class="sb-ch-t">Voice</span></div>
    <p><b>Tone</b> — 静か。断定しすぎない。証拠で語る。主役はいつもユーザー。</p>
    <div class="sb-vpair">
      <div class="sb-vrow"><span class="no">❌ AI analyzed your code.</span> → <span class="yes">✅ Your repository remembers.</span></div>
      <div class="sb-vrow"><span class="no">❌ Powered by AI.</span> → <span class="yes">✅ Built on memory.</span></div>
      <div class="sb-vrow"><span class="no">❌ Your score is 66.</span> → <span class="yes">✅ 49 → 66 — the trajectory, not the number.</span></div>
      <div class="sb-vrow"><span class="no">❌ We found 12 problems.</span> → <span class="yes">✅ Start here.</span></div>
      <div class="sb-vrow"><span class="no">❌ Get the best code review.</span> → <span class="yes">✅ A place where software can be read.</span></div>
    </div>
    <ul>
      <li><b>Evidence over marketing</b> — 「すごい」でなく 49 → 66 と見せる。</li>
      <li><b>Never claim what isn't measured</b> — 確認できたことだけ書き、残りは正直に「分析中」。</li>
      <li><i>Not a score. A character sheet.</i>（Repository DNA）</li>
    </ul>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch06">
    <div class="sb-ch-h"><span class="sb-ch-n">06</span><span class="sb-ch-t">Motion</span></div>
    <p class="sb-big">95% isn't animation. It's anticipation.<br>Warmth over spectacle. Presence over polish.</p>
    <p>Motion は「300ms / ease-in-out」から始めない。<b>思想から始める。</b></p>
    <ul>
      <li><b>95%</b> — 完了を 95%→100% で 750ms 遅らせる。速さでなく"終わった気持ちよさ"＝演出は機能。</li>
      <li><b>Review Scan</b> — 解析を「今読んでいる」体験に（SSE で一行ずつ）。</li>
      <li><b>Animation principles</b> — 見えないほどよく効く。派手さより温度。</li>
    </ul>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch07">
    <div class="sb-ch-h"><span class="sb-ch-n">07</span><span class="sb-ch-t">Character — CodeLens-kun</span></div>
    <p><i>設定集ではない。出演ルール。キャラクターでなく、インターフェースの振る舞い。</i></p>
    <ul>
      <li><b>Never explains first.</b></li>
      <li><b>Appears after the review.</b></li>
      <li><b>Never blocks information.</b></li>
      <li><b>Smiles only after completion.</b></li>
    </ul>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch08">
    <div class="sb-ch-h"><span class="sb-ch-n">08</span><span class="sb-ch-t">Components</span></div>
    <ul>
      <li><b>Workspace</b> — 読む場所（編集部の入口）</li>
      <li><b>Library</b> — 製本された本が並ぶ棚</li>
      <li><b>Book / DNA Card / Review Card</b> — 読み物の単位</li>
    </ul>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch09">
    <div class="sb-ch-h"><span class="sb-ch-n">09</span><span class="sb-ch-t">Artwork</span></div>
    <p>Banner / Hero / Social / OG Image / GitHub Cover。</p>
    <p class="sb-soon">Living document · Being refined.</p>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch10">
    <div class="sb-ch-h"><span class="sb-ch-n">10</span><span class="sb-ch-t">Principles in Practice</span></div>
    <p><i>思想 → ルール → 実装例。誰が作っても CodeLensAI になるための橋。</i></p>
    <ul>
      <li><b>95%</b> · <i>95% isn't animation. It's anticipation.</i> → Review finish / Workspace / Hero / Button</li>
      <li><b>Voice</b> · <i>Evidence over marketing.</i> → <span class="sb-vrow"><span class="no">❌ Powered by AI</span> → <span class="yes">✅ Built on memory</span></span></li>
      <li><b>Character</b> · <i>Never speaks first.</i> → Empty state / Review / Error / Loading</li>
    </ul>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>

  <section class="sb-ch" id="ch11">
    <div class="sb-ch-h"><span class="sb-ch-n">11</span><span class="sb-ch-t">Future</span></div>
    <p class="sb-big">Not rules. Directions.</p>
    <p>これからブランドをどこへ育てたいか。ルールでなく、方向。</p>
    <a class="sb-top" href="#sbTop">↑ Index</a>
  </section>
</div>
@endsection
