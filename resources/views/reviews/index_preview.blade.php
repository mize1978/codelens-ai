@extends('layouts.app_preview')
@section('title', 'CodeLensAI — Live Workspace')

@section('content')

{{-- 🌌 sky: 世界(#81) を部屋の背後にずっと残す ── モックの .sky --}}
<div class="sky"></div>

{{-- 🚪 portal: 入口の演出(~1.25s) → 消える ── モックの .portal（LP CTA → this → the room, one door）--}}
<div class="portal" id="portal">
  <div class="portal-sweep"></div>
  <div class="portal-word">Repository Intelligence</div>
</div>
<style>
  .sky{position:fixed; inset:0; z-index:0; pointer-events:none; background:#05070f}
  .sky::before{content:""; position:absolute; inset:0; background-image:url("/images/hero-entrance.jpg"); background-size:cover; background-position:center 20%; opacity:.30; filter:saturate(.95)}
  .sky::after{content:""; position:absolute; inset:0; background:linear-gradient(180deg, rgba(11,14,20,.28) 0%, rgba(11,14,20,.58) 34%, rgba(11,14,20,.74) 62%, rgba(11,14,20,.90) 84%, #0b0e14 100%)}
  .portal{position:fixed; inset:0; z-index:99990; overflow:hidden; background:#05070f; animation:portalOut 1.25s cubic-bezier(.4,0,.2,1) forwards}
  .portal::before{content:""; position:absolute; inset:0; background-image:url("/images/hero-entrance.jpg"); background-size:cover; background-position:center 20%; opacity:.62; animation:portalDim 1.25s ease forwards}
  .portal::after{content:""; position:absolute; inset:0; background:radial-gradient(ellipse at 50% 46%,transparent 28%,rgba(5,7,15,.62) 100%)}
  .portal-word{position:absolute; top:43%; left:0; right:0; text-align:center; z-index:2; font-family:'JetBrains Mono',monospace; font-size:14px; letter-spacing:5px; text-transform:uppercase; color:#cdd8ff; opacity:0; animation:wordIn 1.25s ease forwards}
  .portal-sweep{position:absolute; top:0; bottom:0; width:38%; left:-38%; z-index:1; background:linear-gradient(90deg,transparent,rgba(150,175,255,.20),transparent); filter:blur(7px); animation:sweep 1.25s ease forwards}
  .portal.gone{display:none}
  @keyframes portalOut{0%,70%{opacity:1}100%{opacity:0; visibility:hidden}}
  @keyframes portalDim{0%{opacity:.62}55%{opacity:.5}100%{opacity:.30}}
  @keyframes wordIn{0%{opacity:0; transform:translateY(7px); letter-spacing:8px}18%{opacity:1; transform:translateY(0); letter-spacing:5px}58%{opacity:1}100%{opacity:0; transform:translateY(-11px)}}
  @keyframes sweep{0%,28%{left:-38%}84%{left:100%}100%{left:100%}}
  @media(prefers-reduced-motion:reduce){.portal{animation:none; opacity:0; visibility:hidden}}
</style>
<script>
  // sky/portal を body直下へ（ヘッダーのz-indexを越えるため）。portalは~1.3sで消す。CSSだけでも消える。
  (function(){
    var sky=document.querySelector('.sky'), portal=document.getElementById('portal');
    if(sky) document.body.appendChild(sky);
    if(portal){
      document.body.appendChild(portal);
      var reduce=window.matchMedia('(prefers-reduced-motion:reduce)').matches;
      if(reduce){portal.classList.add('gone');}
      else{setTimeout(function(){portal.classList.add('gone');},1300);}
    }
  })();
</script>

<div style="max-width:860px;margin:0 auto;padding:22px 20px 60px">

  {{-- Hero ── app-entry_10 の Live Workspace 版（ロゴは上部バーにあるので割愛） --}}
  <div style="text-align:center;margin-bottom:16px">
    <p class="ai-system-label">REPOSITORY INTELLIGENCE</p>
    <h1 style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif;font-size:clamp(26px,5vw,34px);font-weight:700;letter-spacing:-0.5px;line-height:1.15;margin:8px 0 0;background:linear-gradient(180deg,#ffffff,#c4d2ec);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;filter:drop-shadow(0 3px 22px rgba(88,166,255,0.18))">Live Workspace</h1>
    <p style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif;font-size:14.5px;color:var(--text-dim);margin:14px auto 0;max-width:420px;line-height:1.6">GitHub のリポジトリを、セキュリティ・品質・保守性の3軸で解析します。</p>
  </div>

  {{-- 🛸 in-place コンソール（console.html 移植・入力→解析→結果着地まで一枚のカードで・画面遷移なし）--}}
  <div class="console" id="cl-console" style="margin-bottom:28px">
    <div class="console-bar">
      <span class="dot r"></span><span class="dot y"></span><span class="dot g"></span>
      <span class="lbl">codelens · live</span>
    </div>
    <div class="console-body">
      <div class="field-label">Repository URL</div>
      <div class="input-row">
        <input class="repo-input" id="repo" spellcheck="false"
               placeholder="github.com/your-org/your-repo" value="github.com/mize1978/rewardme">
        <button class="analyze" id="go">Analyze</button>
      </div>
      <div class="hint">パブリックリポジトリを解析します。平均 <b>~8s</b>。</div>
      <div class="chips">
        <span class="chip" data-repo="github.com/mize1978/rewardme">rewardme</span>
        <span class="chip" data-repo="github.com/mize1978/debugme">debugme</span>
        <span class="chip" data-repo="github.com/mize1978/codelens-ai">codelens-ai</span>
      </div>
    </div>
    <div class="run" id="run">
      <div class="run-inner">
        {{-- ログ＝主役（上）：本物の progress_step が流れる ＋ バー ＋ ％ --}}
        <div class="run-console" id="run-console"></div>
        <div class="run-bar-wrap"><div class="run-bar" id="run-bar"></div></div>
        <div class="run-pct" id="run-pct">0%</div>
        {{-- 3マントラ＝ナビゲーション（下） --}}
        <div class="steps" id="steps">
          <div class="step" data-verb="Read"><div class="tick"></div><div class="grow"><span class="verb">Read</span> — cloning &amp; parsing the tree</div></div>
          <div class="step" data-verb="Understand"><div class="tick"></div><div class="grow"><span class="verb">Understand</span> — security · quality · maintainability</div></div>
          <div class="step" data-verb="Remember"><div class="tick"></div><div class="grow"><span class="verb">Remember</span> — writing to the repository's memory</div></div>
        </div>
      </div>
      <style>
        .run-console{ margin-top:0; background:rgba(6,8,14,.6); border:1px solid var(--line); border-radius:10px; padding:11px 14px; height:104px; overflow:hidden; font-family:var(--mono); font-size:12px; line-height:1.75; color:#3fb950; display:flex; flex-direction:column; justify-content:flex-end }
        .run-console .cl{ opacity:0; animation:clIn .3s ease forwards; white-space:nowrap; overflow:hidden; text-overflow:ellipsis }
        @keyframes clIn{ from{opacity:0; transform:translateY(3px)} to{opacity:1; transform:none} }
        .run-bar-wrap{ margin-top:14px; height:6px; background:rgba(255,255,255,.06); border-radius:99px; overflow:hidden }
        .run-bar{ position:relative; height:100%; width:0; background:linear-gradient(90deg,var(--info),#8a6dfc); border-radius:99px; transition:width .45s ease; box-shadow:0 0 12px rgba(88,166,255,.45); overflow:hidden }
        .run-bar::after{ content:''; position:absolute; inset:0; background:linear-gradient(90deg,transparent,rgba(255,255,255,.4),transparent); transform:translateX(-100%); animation:barShim 1.5s linear infinite }
        @keyframes barShim{ to{ transform:translateX(100%) } }
        .run-pct{ margin-top:7px; font-family:var(--mono); font-size:11.5px; color:var(--mut); letter-spacing:.05em }
      </style>
      <div class="proof" id="proof">
        <div class="complete">✓ Review Complete</div>
        <div class="result-hero" id="hero">
          <div class="hero-ring">
            <svg viewBox="0 0 220 220" width="220" height="220">
              <circle cx="110" cy="110" r="96" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="8"></circle>
              <circle id="ringfill" cx="110" cy="110" r="96" fill="none" stroke="#3fb950" stroke-width="8" stroke-linecap="round" stroke-dasharray="603" stroke-dashoffset="603" transform="rotate(-90 110 110)"></circle>
            </svg>
            <div class="hero-center">
              <div class="hero-score" id="score">0</div>
              <div class="hero-grade" id="grade">GOOD</div>
            </div>
          </div>
          <div class="hero-repo" id="repoName">rewardme</div>
          <div class="hero-stars" id="heroStars"></div>
        </div>
        <div class="result-rest" id="rest">
          {{-- Repository Insight（本物のAI総評・辛辣な名刺）── Score → Memory の橋渡し --}}
          <div class="insight" id="insightBox">
            <div class="insight-label">Repository Insight</div>
            <p class="insight-text" id="insightText">—</p>
          </div>
          {{-- Memory Card と exit は「本編の最後（締め）」へ移動（レポートの下）--}}
        </div>
      </div>
    </div>
  </div>
  <style>
    /* console.html トークン（追加・--purple はレイアウトと衝突するので使用箇所は直値）*/
    :root{ --info:#58a6ff; --good:#3fb950; --warn:#d6a531; --crit:#f2585a; --ink:#e8edf5; --sub:#9aa4b8; --mut:#6b7488; --line:#252b3a; --line2:#2f3547;
      --sans:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif; --mono:'JetBrains Mono','SF Mono',ui-monospace,Menlo,monospace; }
    .console{ position:relative; background:rgba(18,22,31,.58); -webkit-backdrop-filter:blur(16px) saturate(1.18); backdrop-filter:blur(16px) saturate(1.18);
      border:1px solid rgba(122,142,182,.17); border-radius:18px; overflow:hidden; box-shadow:0 34px 90px rgba(0,0,0,.30), inset 0 1px 0 rgba(255,255,255,.05); transition:border-color .5s ease, box-shadow .5s ease }
    .console::before{ content:""; position:absolute; inset:0; pointer-events:none; z-index:0; background:radial-gradient(120% 85% at 18% -12%, rgba(118,140,255,.11), transparent 58%), radial-gradient(100% 70% at 96% -4%, rgba(150,95,255,.08), transparent 54%) }
    .console > *{ position:relative; z-index:1 }
    .console.analyzing{ border-color:rgba(96,124,224,.48); box-shadow:0 34px 100px rgba(30,45,110,.40), inset 0 1px 0 rgba(255,255,255,.06) }
    .console-bar{ display:flex; align-items:center; gap:8px; padding:11px 15px; background:rgba(13,17,26,.5); border-bottom:1px solid rgba(37,43,58,.7) }
    .console-bar .dot{ width:10px; height:10px; border-radius:50% } .console-bar .r{ background:#f2585a } .console-bar .y{ background:#d6a531 } .console-bar .g{ background:#3fb950 }
    .console-bar .lbl{ margin-left:8px; font-family:var(--mono); font-size:11.5px; color:var(--mut); letter-spacing:.3px }
    .console-body{ padding:26px 26px 28px }
    .field-label{ font-size:12px; color:var(--mut); font-family:var(--mono); letter-spacing:.4px; text-transform:uppercase; margin-bottom:10px }
    .input-row{ display:flex; gap:12px } @media(max-width:520px){ .input-row{ flex-direction:column } }
    .repo-input{ flex:1; background:#0a0d14; border:1px solid var(--line2); border-radius:12px; color:var(--ink); font-family:var(--mono); font-size:14.5px; padding:15px 16px; outline:none; transition:border-color .15s, box-shadow .15s }
    .repo-input::placeholder{ color:#4d5568 } .repo-input:focus{ border-color:#3a4a7a; box-shadow:0 0 0 3px rgba(90,110,255,.12) }
    .analyze{ background:linear-gradient(135deg,#6d5efc,#2f8bff); color:#fff; border:none; font-family:var(--sans); font-weight:650; font-size:15px; padding:0 30px; border-radius:12px; cursor:pointer; white-space:nowrap; box-shadow:0 8px 26px rgba(90,110,255,.30); transition:transform .15s, filter .15s }
    .analyze:hover{ transform:translateY(-1px); filter:brightness(1.06) } .analyze:active{ transform:translateY(0) } .analyze:disabled{ opacity:.55; cursor:default; transform:none; filter:none }
    .hint{ margin-top:16px; font-size:12.5px; color:var(--mut) } .hint b{ color:var(--sub); font-weight:500; font-family:var(--mono) }
    .chips{ display:flex; gap:8px; margin-top:14px; flex-wrap:wrap }
    .chip{ font-family:var(--mono); font-size:12px; color:var(--sub); background:#0d111a; border:1px solid var(--line); border-radius:20px; padding:6px 13px; cursor:pointer; transition:all .15s } .chip:hover{ border-color:var(--line2); color:var(--ink) }
    .run{ max-height:0; overflow:hidden; transition:max-height .6s ease } .run.open{ max-height:1600px } .run-inner{ padding:20px 26px 8px; border-top:1px solid var(--line) }
    .steps{ border-top:1px solid var(--line); padding-top:18px; margin-top:22px }
    .step{ display:flex; align-items:center; gap:12px; padding:9px 0; font-family:var(--mono); font-size:13px; color:var(--mut); opacity:.35; transition:opacity .3s, color .3s }
    .step.active{ opacity:1; color:var(--sub) } .step.done{ opacity:1; color:var(--ink) }
    .step .tick{ width:18px; height:18px; border-radius:50%; border:1.5px solid var(--line2); display:grid; place-items:center; flex:none; font-size:11px; transition:all .3s }
    .step.active .tick{ border-color:var(--info); color:var(--info); animation:spin 1s linear infinite } .step.done .tick{ border-color:var(--good); background:var(--good); color:#0b0e14 }
    .step .grow{ flex:1 } .step .verb{ color:inherit }
    .proof{ margin-top:8px; border-top:1px solid var(--line); padding:22px 26px 26px; display:none } .proof.show{ display:block; animation:rise .6s ease }
    .complete{ font-family:var(--mono); font-size:12px; letter-spacing:.5px; color:var(--good); text-transform:uppercase; margin-bottom:6px }
    .result-hero{ position:relative; text-align:center; padding:10px 0 6px }
    .result-hero::before{ content:""; position:absolute; top:6px; left:50%; width:280px; height:280px; transform:translateX(-50%) scale(.6); border-radius:50%; pointer-events:none; opacity:0; background:radial-gradient(circle, var(--glow,rgba(63,185,80,.20)), transparent 62%) }
    .hero-ring{ position:relative; width:220px; height:220px; margin:0 auto } .hero-ring svg{ display:block }
    .hero-center{ position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center }
    .hero-score{ font-family:var(--sans); font-weight:800; font-size:78px; line-height:1; letter-spacing:-2.5px; color:var(--good) }
    .hero-grade{ font-family:var(--mono); font-size:17px; letter-spacing:3.5px; text-transform:uppercase; margin-top:8px; color:var(--good) }
    .hero-repo{ font-family:var(--mono); font-size:14px; color:var(--sub); margin-top:16px }
    .result-hero.land::before{ animation:glowBurst 1.1s ease both } .result-hero.land .hero-center{ animation:landIn .62s cubic-bezier(.2,.85,.25,1.15) both } .result-hero.land .hero-grade{ animation:gradeIgnite .55s .32s ease both }
    @keyframes glowBurst{ 0%{opacity:0; transform:translateX(-50%) scale(.6)} 42%{opacity:1; transform:translateX(-50%) scale(1.02)} 100%{opacity:.5; transform:translateX(-50%) scale(1)} }
    @keyframes landIn{ 0%{transform:scale(1.4); opacity:0} 55%{opacity:1} 100%{transform:scale(1)} }
    @keyframes gradeIgnite{ from{opacity:0; transform:translateY(4px)} to{opacity:1; transform:none} }
    .result-rest{ opacity:0; transform:translateY(12px); transition:opacity .6s ease, transform .6s ease } .result-rest.show{ opacity:1; transform:none }
    .insight{ margin-top:22px; padding-top:20px; border-top:1px solid var(--line) }
    .insight-label{ font-family:var(--mono); font-size:11px; letter-spacing:2px; text-transform:uppercase; color:var(--info); margin-bottom:11px }
    .insight-text{ font-size:16px; line-height:1.85; color:#c9d3e4; margin:0; font-family:var(--sans) }
    .memory-stamp{ position:relative; display:flex; align-items:flex-start; gap:13px; margin-top:20px; padding:16px 18px; border-radius:12px; background:linear-gradient(135deg,rgba(124,108,255,.12),rgba(47,139,255,.05)); border:1px solid var(--line2) }
    .grow-tag{ position:absolute; top:13px; right:15px; font-family:var(--mono); font-size:11px; letter-spacing:1.5px; text-transform:uppercase; color:var(--good); text-shadow:0 0 12px rgba(63,185,80,.5); opacity:0; transform:translateY(-3px); transition:opacity .5s ease .35s, transform .5s ease .35s }
    .result-rest.show .grow-tag{ opacity:1; transform:none }
    .memory-stamp .ic{ width:30px; height:30px; border-radius:8px; flex:none; margin-top:2px; background:radial-gradient(circle at 50% 35%,#1c2333,#0b0f18); border:1px solid var(--line2); display:grid; place-items:center; font-size:15px }
    .memory-stamp .txt{ font-size:13.5px; line-height:1.55; display:flex; flex-direction:column; gap:4px }
    .memory-stamp .promise{ font-family:var(--mono); font-style:normal; font-size:11.5px; letter-spacing:.3px; color:var(--mut); text-decoration:line-through; text-decoration-color:rgba(107,116,136,.5) }
    .memory-stamp .txt b{ color:var(--ink); font-weight:600 } .memory-stamp .txt span{ color:var(--sub); font-size:12.5px }
    .exit-actions{ display:flex; align-items:center; gap:12px; margin-top:20px; flex-wrap:wrap }
    .view-arch{ display:inline-flex; align-items:center; text-decoration:none; background:linear-gradient(135deg,rgba(124,108,255,.18),rgba(47,139,255,.10)); border:1px solid var(--line2); color:var(--ink); font-family:var(--sans); font-weight:600; font-size:13.5px; padding:11px 20px; border-radius:11px; cursor:pointer; transition:all .15s } .view-arch:hover{ border-color:#8a6dfc; transform:translateY(-1px) }
    .again{ background:none; border:none; color:var(--mut); font-family:var(--mono); font-size:12.5px; padding:8px 6px; cursor:pointer; transition:color .15s } .again:hover{ color:var(--ink) }
    @keyframes rise{ from{opacity:0; transform:translateY(12px)} to{opacity:1; transform:translateY(0)} } @keyframes spin{ to{ transform:rotate(360deg) } }
    /* Analyze中：下の3カードを暗転 */
    body.ws-analyzing .feature-card{ opacity:.32; filter:saturate(.7); pointer-events:none; transition:opacity .45s, filter .45s }
    body.ws-analyzing .footline{ opacity:.5; transition:opacity .45s }
  </style>

  {{-- 🗂 Review Report 本編がここに AJAX で差し込まれる（同じページ・遷移なし・机の上に広げる）--}}
  <div id="report-slot"></div>

  {{-- 📖 Memory Card ＝ 一冊の締め（レポートの最後）--}}
  <div class="memory-stamp" id="memoryCard" style="display:none;margin-top:8px">
    <span class="grow-tag">↗ Grow</span>
    <div class="ic">📖</div>
    <div class="txt">
      <em class="promise">Software deserves a memory.</em>
      <b>This review is now part of the repository's memory.</b>
      <span id="stampSub">Memory Entry #51 · 記録済み。次のレビューは、この履歴の上に積まれる。</span>
    </div>
  </div>
  <div class="exit-actions" id="exitActions" style="display:none;margin-top:18px;margin-bottom:8px">
    <a href="{{ route('review.archive') }}" class="view-arch" id="viewArch">📚 View in Repository Memory →</a>
    <button class="again" id="again">Review another</button>
  </div>

  {{-- ⑤ End of Review ＝ 本を閉じる幕引き（Popular Reviews への急なLP復帰を防ぐ）--}}
  <div class="end-of-review" id="endOfReview">
    <div class="eor-line"></div>
    <div class="eor-text">End of Review</div>
    <div class="eor-sub">This review is now archived.</div>
  </div>
  <style>
    #report-slot{ scroll-margin-top:80px }
    /* ① Review Findings ＝ 本編の"章扉"（説明書っぽさを消す・本編が始まる感）*/
    .report-open{ text-align:center; margin:52px 0 32px }
    .report-open .ro-title{ font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif; font-size:20px; font-weight:700; letter-spacing:.3px; color:#e8edf5 }
    .report-open .ro-underline{ width:54px; height:2px; margin:13px auto 0; background:linear-gradient(90deg,#58a6ff,#8a6dfc); border-radius:2px; box-shadow:0 0 10px rgba(88,166,255,.5) }
    /* ② 概要は脇役に（小さめ・控えめ）→ 主役は Fixes */
    /* ② 概要は脇役＋①クランプ(3行)＋続きを読む */
    #report-slot .summary-text{ font-size:12.5px !important; line-height:1.8 !important; color:#9aa4b8 !important; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden }
    #report-slot .summary-text.expanded{ -webkit-line-clamp:unset }
    #report-slot .summary-more{ display:inline-block; margin-top:10px; font-family:'JetBrains Mono',monospace; font-size:11.5px; color:#58a6ff; cursor:pointer } #report-slot .summary-more:hover{ text-decoration:underline }
    /* ④ Fixes は主役に（明るい・強い枠・グロー）*/
    #report-slot .priorities-card{ background:linear-gradient(180deg,rgba(88,166,255,.07),rgba(18,22,31,.55)) !important; border-color:rgba(88,166,255,.4) !important; box-shadow:0 0 28px rgba(88,166,255,.12) !important }
    #report-slot .priorities-card .section-title{ font-size:1.06em !important }
    /* ③ 本編の余白（詰まり解消・章の呼吸）*/
    #report-slot .github-stats-bar{ margin-bottom:26px !important }
    #report-slot .coverage-bar{ margin-bottom:26px !important }
    #report-slot .scores-row{ margin-bottom:32px !important }
    #report-slot .section-card{ margin-bottom:24px !important }
    /* ④ 良い点 読みやすく */
    #report-slot .bullet-list li{ line-height:1.9 !important; margin-bottom:9px !important }
    /* ② Repository Insight 一段目を強調 */
    .insight-text strong{ color:#e8edf5; font-weight:700 }
    .insight-text .insight-rest{ display:block; margin-top:8px } /* 唯一の"息継ぎがない場所"を解消 */
    /* ⑥ Memory 締め演出（発光）*/
    #memoryCard.memory-land{ animation:memGlow 1.7s ease }
    @keyframes memGlow{ 0%{box-shadow:0 0 0 rgba(63,185,80,0)} 35%{box-shadow:0 0 34px rgba(63,185,80,.4)} 100%{box-shadow:0 0 0 rgba(63,185,80,0)} }
    /* ⑤ End of Review 幕引き（本を閉じる）*/
    .end-of-review{ text-align:center; margin:44px 0 6px; opacity:0; transition:opacity .9s ease }
    .end-of-review.show{ opacity:1 }
    .end-of-review .eor-line{ width:40px; height:1px; margin:0 auto 16px; background:var(--line2) }
    .end-of-review .eor-text{ font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:3px; text-transform:uppercase; color:#6b7488 }
    .end-of-review .eor-sub{ font-size:12px; color:#4d5568; margin-top:8px; font-family:'JetBrains Mono',monospace }
    /* ① スコアをヒーローに：星（スコア由来・盛らない）*/
    .hero-stars{ font-size:16px; letter-spacing:4px; margin-top:4px } /* 58/FAIR と星を一体化＝バッジ感（10→4px）*/
    /* ④ 解析中は世界(#81)を少し沈めて Workspace に集中／終わると戻る */
    .sky::before{ transition:opacity .7s ease, filter .7s ease }
    body.ws-processing .sky::before{ opacity:.12 !important; filter:saturate(.82) }
    /* Findings の章扉に"ここから本編"の一行 */
    .report-open .ro-sub{ font-size:12px; color:#6b7488; margin-top:14px; font-family:'JetBrains Mono',monospace; letter-spacing:.3px }
    /* ② 要約(Top3・概要)↑ / 全文(Issues…)↓ の区切り */
    .findings-split{ display:flex; align-items:center; gap:14px; margin:38px 0 24px }
    .findings-split::before, .findings-split::after{ content:''; flex:1; height:1px; background:var(--line) }
    .findings-split span{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:2.5px; text-transform:uppercase; color:#6b7488 }
    /* ③ Findings が"次の章"としてフェードで現れる */
    #report-slot.rin{ animation:reportIn .7s ease }
    @keyframes reportIn{ from{opacity:0; transform:translateY(10px)} to{opacity:1; transform:none} }
  </style>

  {{-- Feature cards --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:32px">

    {{-- ⑤ 解析 → 青 --}}
    <div class="panel feature-card fc-analysis">
      <div class="feature-icon-wrap icon-analysis">
        <img src="/images/icon-analysis.png" alt="analysis">
        {{-- ③ スキャンライン overlay --}}
        <div class="scan-line"></div>
      </div>
      <div class="feature-title">高速解析</div>
      <div class="feature-desc">ファイルツリーを自動スキャンし、重要ファイルを選択してレビュー</div>
    </div>

    {{-- ⑤ セキュリティ → 紫 --}}
    <div class="panel feature-card fc-security">
      <div class="feature-icon-wrap icon-security">
        <img src="/images/icon-security.png" alt="security">
        {{-- ③ チェックマーク overlay --}}
        <svg class="check-overlay" viewBox="0 0 24 24" fill="none">
          <path class="check-path" d="M5 13l4 4L19 7" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="28" stroke-dashoffset="28"/>
        </svg>
      </div>
      <div class="feature-title">セキュリティ</div>
      <div class="feature-desc">脆弱性・セキュリティリスクをAIが検出して深刻度順に警告</div>
    </div>

    {{-- ⑤ リファクタ → 青紫 --}}
    <div class="panel feature-card fc-refactor">
      <div class="feature-icon-wrap icon-refactor">
        <img src="/images/icon-refactor.png" alt="refactor">
        {{-- ③ 線が左から右へ流れる --}}
        <svg class="branch-overlay" viewBox="0 0 50 12" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="branchGrad" x1="0" y1="0" x2="1" y2="0">
              <stop offset="0%" stop-color="#4488ff"/>
              <stop offset="100%" stop-color="#9944ff"/>
            </linearGradient>
          </defs>
          <polyline points="2,6 43,6 39,3 43,6 39,9"
            stroke="url(#branchGrad)" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </svg>
      </div>
      <div class="feature-title">リファクタ</div>
      <div class="feature-desc">具体的なコード改善提案と品質・保守性スコアを提示</div>
    </div>

  </div>

  {{-- Read → Understand → Remember → Grow（console.html chaser・解析中に順に灯り・結果で Grow が緑に）--}}
  <div class="footline" id="chaser">
    <span class="v" data-v="0">Read</span><i>→</i><span class="v" data-v="1">Understand</span><i>→</i><span class="v" data-v="2">Remember</span><i>→</i><span class="v grow" data-v="3">Grow</span>
  </div>
  <style>
    .footline{ text-align:center; margin-top:34px; margin-bottom:6px; font-size:12px; font-family:'JetBrains Mono',monospace; letter-spacing:.3px }
    .footline .v{ color:#39414f; transition:color .45s ease, text-shadow .45s ease }
    .footline i{ font-style:normal; color:#2a3140; margin:0 9px }
    .footline .v.on{ color:#e8edf5 }
    .footline .v.grow.on{ color:#3fb950; text-shadow:0 0 14px rgba(63,185,80,.55) }
  </style>

  {{-- Popular Reviews --}}
  @if($popular->count())
  <div>
    <p class="section-title">⭐ Popular Reviews</p>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px">
      @foreach($popular as $review)
      @php
        $stars = max(1, min(5, round($review->overall_score / 20)));
        $fw = $review->review_data['framework'] ?? null;
        $lang = $review->language ?? ($review->review_data['language'] ?? null);
        $circleMap = ['#00ff88' => 'sc-green','#4488ff' => 'sc-blue','#ffaa00' => 'sc-yellow','#ff4466' => 'sc-red'];
        $circleClass = $circleMap[$review->score_color] ?? 'sc-blue';
      @endphp
      <a href="{{ route('reviews.show', $review) }}" class="popular-card">
        <div class="pc-top">
          <span class="pc-repo">{{ $review->owner }}/<strong>{{ $review->repo }}</strong></span>
          {{-- ④ 円バッジ --}}
          <span class="score-circle {{ $circleClass }}">{{ $review->overall_score }}</span>
        </div>
        <div class="pc-stars">
          @for($i = 1; $i <= 5; $i++)
            <span style="color:{{ $i <= $stars ? '#ffcc00' : 'rgba(255,255,255,0.15)' }}">★</span>
          @endfor
        </div>
        @php $verdict = $review->verdictExcerpt(78); @endphp
        @if($verdict)
        <div class="pc-says">
          <span class="pc-says-label">CodeLens says</span>
          <span class="pc-says-text">{{ $verdict }}</span>
        </div>
        @endif
        <div class="pc-tags">
          @if($lang)<span class="pc-tag">{{ $lang }}</span>@endif
          @if($fw && $fw !== 'Unknown' && $fw !== 'なし')<span class="pc-tag">{{ $fw }}</span>@endif
          <span class="pc-tag pc-views">👁 {{ $review->view_count }}</span>
        </div>
      </a>
      @endforeach
    </div>
  </div>
  @endif

</div>

<style>
/* ===== Feature cards ===== */
.feature-card {
  padding: 22px 18px; text-align: center;
  transition: border-color 0.25s, transform 0.25s, box-shadow 0.25s;
}
/* ② hover — 浮かせる */
.feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.4); }

/* ⑤ カード色分け */
.fc-analysis { border-color: rgba(0,180,255,0.2); }
.fc-analysis:hover { border-color: rgba(0,180,255,0.55); box-shadow: 0 12px 32px rgba(0,150,255,0.15); }

.fc-security { border-color: rgba(153,68,255,0.2); }
.fc-security:hover { border-color: rgba(153,68,255,0.55); box-shadow: 0 12px 32px rgba(153,68,255,0.15); }

.fc-refactor { border-color: rgba(80,100,255,0.2); }
.fc-refactor:hover { border-color: rgba(80,100,255,0.55); box-shadow: 0 12px 32px rgba(80,100,255,0.15); }

/* ===== app-entry_10 のカード背景＋文字色に統一（グラデ枠 translucent ガラス）===== */
.feature-card{
  border:1px solid transparent !important; border-radius:14px !important;
  background:
    linear-gradient(180deg,rgba(27,33,48,.85),rgba(15,19,28,.8)) padding-box,
    linear-gradient(135deg,rgba(88,166,255,.6),rgba(138,109,252,.55) 55%,rgba(88,166,255,.35)) border-box !important;
  box-shadow:0 0 24px rgba(88,166,255,.12), 0 14px 34px rgba(0,0,0,.42), inset 0 0 26px rgba(88,166,255,.055) !important;
}
.fc-analysis, .fc-security, .fc-refactor,
.fc-analysis:hover, .fc-security:hover, .fc-refactor:hover{ border-color:transparent !important }
.feature-card:hover{
  transform:translateY(-4px) !important;
  box-shadow:0 0 34px rgba(88,166,255,.24), 0 22px 48px rgba(0,0,0,.5), inset 0 0 30px rgba(88,166,255,.09) !important;
}
.feature-title{ color:#e8edf5 !important; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif !important }
.feature-desc{ color:#9aa4b8 !important; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif !important }

/* ① アイコンサイズ 74px + ② ホバーで拡大 */
.feature-icon-wrap {
  width: 74px; height: 74px; margin: 0 auto 16px;
  position: relative; overflow: visible;
  transition: transform 0.25s;
}
.feature-card:hover .feature-icon-wrap {
  transform: translateY(-3px) scale(1.05);
}
.feature-icon-wrap img {
  width: 100%; height: 100%; object-fit: contain;
  transition: filter 0.3s;
}
.fc-analysis:hover .feature-icon-wrap img  { filter: drop-shadow(0 0 10px rgba(0,180,255,0.45)); }
.fc-security:hover .feature-icon-wrap img  { filter: drop-shadow(0 0 10px rgba(153,68,255,0.45)); }
.fc-refactor:hover .feature-icon-wrap img  { filter: drop-shadow(0 0 10px rgba(80,100,255,0.45)); }

.feature-title { font-size: 0.82rem; font-weight: 700; margin-bottom: 6px; color: #fff; }
.feature-desc { font-size: 0.68rem; color: var(--text-dim); line-height: 1.6; }

/* ③ 解析 — スキャンライン */
.scan-line {
  position: absolute; top: 48%; left: -100%;
  width: 100%; height: 2px;
  background: linear-gradient(90deg, transparent, rgba(0,220,255,0.9) 50%, transparent);
  pointer-events: none; opacity: 0;
}
.fc-analysis:hover .scan-line {
  animation: scanAnim 0.55s ease-out forwards;
}
@keyframes scanAnim {
  0%   { left: -100%; opacity: 1; }
  100% { left: 110%;  opacity: 0; }
}

/* ③ セキュリティ — チェック描画 */
.check-overlay {
  position: absolute; bottom: -2px; right: -2px;
  width: 22px; height: 22px; overflow: visible; pointer-events: none;
}
.check-path { transition: none; }
.fc-security:hover .check-path {
  animation: drawCheck 0.5s ease-out 0.05s forwards;
}
@keyframes drawCheck {
  to { stroke-dashoffset: 0; }
}

/* ③ リファクタ — 線が流れる (clip-path reveal) */
.branch-overlay {
  position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
  width: 54px; height: 14px; overflow: visible; pointer-events: none;
  clip-path: inset(0 100% 0 0);
}
.fc-refactor:hover .branch-overlay {
  animation: revealLine 0.5s ease-out forwards;
}
@keyframes revealLine {
  from { clip-path: inset(0 100% 0 0); }
  to   { clip-path: inset(0 -4px 0 0); }
}

/* ④ 円バッジ */
.score-circle {
  width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem; font-weight: 900; font-family: monospace;
  border: 1.5px solid;
}
.sc-green  { color: #00ff88; border-color: rgba(0,255,136,0.5);  background: rgba(0,255,136,0.08); }
.sc-blue   { color: #4488ff; border-color: rgba(68,136,255,0.5); background: rgba(68,136,255,0.08); }
.sc-yellow { color: #ffaa00; border-color: rgba(255,170,0,0.5);  background: rgba(255,170,0,0.08); }
.sc-red    { color: #ff4466; border-color: rgba(255,68,102,0.5); background: rgba(255,68,102,0.08); }

/* 対応言語チップ */
.lang-chips { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-top: 14px; }
.lang-chip {
  font-size: 0.65rem; padding: 3px 10px; border-radius: 99px;
  background: rgba(0,200,255,0.06); border: 1px solid rgba(0,200,255,0.14);
  color: var(--text-mute); letter-spacing: 0.06em; font-weight: 500;
}

/* 入力欄スキャンビーム */
.input-wrap { position: relative; overflow: hidden; border-radius: 6px; }
.input-scan-beam {
  position: absolute; top: 0; left: -60%;
  width: 55%; height: 100%;
  background: linear-gradient(90deg, transparent 0%, rgba(0,200,255,0.18) 50%, transparent 100%);
  pointer-events: none; opacity: 0;
}
.input-wrap.scanning .input-scan-beam {
  animation: inputBeam 0.55s ease-out forwards;
}
@keyframes inputBeam {
  0%   { left: -60%; opacity: 1; }
  100% { left: 110%; opacity: 0.6; }
}

/* Popular cards */
.popular-card {
  display: block; padding: 14px 16px;
  background: var(--bg3); border: 1px solid var(--border);
  border-radius: 10px; transition: border-color 0.2s, transform 0.2s;
  text-decoration: none;
}
.popular-card:hover {
  border-color: rgba(0,200,255,0.45);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0,150,255,0.1);
}
.pc-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px; gap: 8px; }
.pc-repo { font-size: 0.78rem; color: var(--text-dim); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pc-repo strong { color: #fff; }
.pc-stars { font-size: 0.8rem; letter-spacing: 1px; margin-bottom: 8px; }
.pc-tags { display: flex; gap: 6px; flex-wrap: wrap; }
.pc-tag {
  font-size: 0.62rem; padding: 2px 7px; border-radius: 4px;
  background: rgba(0,200,255,0.08); border: 1px solid rgba(0,200,255,0.15);
  color: var(--text-dim); font-weight: 600; letter-spacing: 0.05em;
}
.pc-views { background: transparent; border-color: transparent; color: var(--text-mute); }
.pc-says { margin: 2px 0 9px; }
.pc-says-label { display:block; font-size:0.55rem; letter-spacing:0.14em; text-transform:uppercase; color:rgba(0,200,255,0.8); font-weight:700; margin-bottom:3px; }
.pc-says-text { font-size:0.74rem; line-height:1.5; color:rgba(235,240,255,0.92);
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }

/* ===== CodeLens Library ===== */
.library-section {
  margin: 0 -12px 48px;
  padding: 0 12px;
}

/* ─── CodeLens Library ─── */
.lib-title-rail {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}
.lib-title-rail::before,
.lib-title-rail::after {
  content: '';
  flex: 1;
  height: 1px;
}
.lib-title-rail::before {
  background: linear-gradient(90deg, transparent, rgba(0,200,255,0.22));
}
.lib-title-rail::after {
  background: linear-gradient(270deg, transparent, rgba(0,200,255,0.22));
}
.lib-title {
  padding: 0 16px;
  font-size: 0.62rem; font-weight: 700; letter-spacing: 0.35em;
  color: rgba(255,255,255,0.38);
  text-transform: uppercase; white-space: nowrap;
}
.lib-subtitle {
  font-size: 0.60rem; color: rgba(255,255,255,0.18);
  letter-spacing: 0.06em; text-align: center;
  margin: 6px 0 14px; font-style: italic;
}

/* Shelf wrap */
.lib-shelf-wrap { position: relative; }

/* Books grid */
.library-shelf {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 6px;
  align-items: end;
  padding-bottom: 6px;
}

/* ◎──────────────◎ 棚板 */
.lib-shelf-board {
  position: relative;
  height: 2px;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(0,185,255,0.18) 5%,
    rgba(0,205,255,0.58) 50%,
    rgba(0,185,255,0.18) 95%,
    transparent 100%
  );
  box-shadow: 0 0 14px rgba(0,200,255,0.16), 0 1px 1px rgba(0,200,255,0.10);
}
/* ◎ 端のキャップ */
.lib-shelf-board::before,
.lib-shelf-board::after {
  content: '';
  position: absolute;
  top: 50%; transform: translateY(-50%);
  width: 7px; height: 7px;
  border-radius: 50%;
  background: rgba(0,215,255,0.95);
  box-shadow: 0 0 8px rgba(0,200,255,0.85), 0 0 20px rgba(0,200,255,0.40);
}
.lib-shelf-board::before { left: 0; }
.lib-shelf-board::after  { right: 0; }

/* Each book */
.lib-book {
  display: flex; flex-direction: column; align-items: center; gap: 7px;
  text-decoration: none;
}

/* Book image */
.lib-book-img {
  width: 100%; display: block; border-radius: 6px;
  transition: transform 0.22s ease, filter 0.22s ease;
  will-change: transform, filter;
  transform-origin: center bottom;
}

/* ホバー: 6px 浮く + 棚板に影 + 金縁グロー */
.lib-book:hover .lib-book-img {
  transform: translateY(-6px) scale(1.03);
  filter:
    drop-shadow(0 18px 8px rgba(0,0,0,0.50))    /* 棚板への影 */
    drop-shadow(0 0 10px rgba(185,148,52,0.28)); /* 金縁グロー */
}

/* ホバー時のみ現れるラベル */
.lib-book-label {
  display: flex; flex-direction: column; align-items: center; gap: 2px;
  min-height: 30px; /* レイアウトシフト防止 */
  opacity: 0;
  transform: translateY(4px);
  transition: opacity 0.18s ease, transform 0.18s ease;
  pointer-events: none;
}
.lib-book:hover .lib-book-label {
  opacity: 1;
  transform: translateY(0);
}
.lib-book-name {
  font-size: 0.58rem; font-weight: 700; letter-spacing: 0.06em;
  color: rgba(255,255,255,0.65); text-align: center;
}
.lib-book-open {
  font-size: 0.50rem; letter-spacing: 0.14em;
  color: rgba(0,200,255,0.60);
}

/* Archive — 静かに眠っている */
.lib-book--archive .lib-book-img {
  filter: saturate(0.25) brightness(0.62);
}
.lib-book--archive:hover .lib-book-img {
  transform: translateY(-5px) scale(1.01);
  transition: transform 0.35s ease, filter 0.35s ease;
  filter: saturate(0.38) brightness(0.75);
}

/* Lock wrap — positions the 🔒 badge */
.lib-book-lock-wrap {
  position: relative;
  width: 100%;
}

/* Locked books — still alive, barely touched */
.lib-book--locked .lib-book-img {
  filter: saturate(0.90);
}
.lib-book--locked:hover .lib-book-img {
  transform: translateY(-6px) scale(1.03);
  filter:
    saturate(0.92)
    drop-shadow(0 18px 8px rgba(0,0,0,0.50))
    drop-shadow(0 0 10px rgba(185,148,52,0.28));
}
.lib-book--locked .lib-book-open--locked {
  color: rgba(160,200,255,0.50);
  font-size: 0.48rem;
  letter-spacing: 0.12em;
}

/* 🔒 badge — silver-blue SF lock */
.lib-book-lock-icon {
  position: absolute;
  bottom: 4px; right: 4px;
  font-size: 0.60rem;
  line-height: 1;
  pointer-events: none;
  filter:
    grayscale(1) brightness(2.2)
    drop-shadow(0 0 4px rgba(140,200,255,0.90))
    drop-shadow(0 0 2px rgba(0,0,0,0.70));
}

/* Locked modal overlay */
.lib-locked-modal {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(0,0,0,0.72);
  backdrop-filter: blur(6px);
  align-items: center;
  justify-content: center;
}
.lib-locked-modal.is-open {
  display: flex;
}
.lib-locked-modal-box {
  background: #0e1621;
  border: 1px solid rgba(0,185,255,0.18);
  border-radius: 14px;
  padding: 36px 40px;
  max-width: 340px;
  width: 90%;
  text-align: center;
  box-shadow: 0 0 40px rgba(0,150,255,0.12), 0 8px 32px rgba(0,0,0,0.6);
}
.lib-locked-modal-icon {
  font-size: 2rem;
  margin-bottom: 14px;
  display: block;
}
.lib-locked-modal-title {
  font-size: 0.90rem;
  font-weight: 700;
  color: rgba(255,255,255,0.88);
  margin-bottom: 10px;
  letter-spacing: 0.04em;
}
.lib-locked-modal-desc {
  font-size: 0.74rem;
  color: rgba(160,190,220,0.70);
  line-height: 1.6;
  margin-bottom: 22px;
}
.lib-locked-modal-close {
  background: rgba(0,185,255,0.12);
  border: 1px solid rgba(0,185,255,0.25);
  color: rgba(0,200,255,0.80);
  font-size: 0.72rem;
  letter-spacing: 0.10em;
  padding: 8px 22px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.18s ease, color 0.18s ease;
}
.lib-locked-modal-close:hover {
  background: rgba(0,185,255,0.22);
  color: rgba(0,220,255,1);
}

/* Responsive */
@media (max-width: 640px) {
  .library-shelf { grid-template-columns: repeat(3, 1fr); gap: 8px; }
  .library-section { margin: 0 0 44px; padding: 0; }
}
@media (max-width: 380px) {
  .library-shelf { grid-template-columns: repeat(2, 1fr); }
}
</style>

<script>
// 🛸 in-place Analyze（console.html のロジック＋本物のbackend配線・画面遷移なし）
(function(){
  var reduce = window.matchMedia('(prefers-reduced-motion:reduce)').matches;
  var consoleEl = document.getElementById('cl-console');
  var run = document.getElementById('run');
  var proof = document.getElementById('proof');
  var steps = [].slice.call(document.querySelectorAll('#steps .step'));
  var chaser = [].slice.call(document.querySelectorAll('#chaser .v'));
  var ringfill = document.getElementById('ringfill');
  var scoreEl = document.getElementById('score');
  var gradeEl = document.getElementById('grade');
  var repoNameEl = document.getElementById('repoName');
  var repo = document.getElementById('repo');
  var go = document.getElementById('go');
  var again = document.getElementById('again');
  var viewArch = document.getElementById('viewArch');
  var hero = document.getElementById('hero');
  var rest = document.getElementById('rest');
  if(!go || !consoleEl) return;
  var meta = document.querySelector('meta[name=csrf-token]');
  var CSRF = meta ? meta.content : '{{ csrf_token() }}';
  var runConsole=document.getElementById('run-console'), runBar=document.getElementById('run-bar'), runPct=document.getElementById('run-pct');
  var _busy=false, _curStep='', _animPct=0, _targetPct=5, _anTimer=null, _barTimer=null;
  // progress_step（本物）→ {pct, 3マントラのどれ, ログ}
  var STEPS={
    pending:            {pct:8,  step:0, log:'[QUEUE]  解析待ち…'},
    fetching_repository:{pct:24, step:0, log:'[GITHUB] GitHubへ接続中…'},
    reading_files:      {pct:44, step:0, log:'[READ]   ソースコードを読み込み中…'},
    analyzing:          {pct:66, step:1, log:'[CLAUDE] AIがレビュー中…'},
    generating_report:  {pct:88, step:2, log:'[MEMORY] リポジトリの記憶に書き込み中…'}
  };
  var ANALYZING_LINES=['[CLAUDE] 設計パターンを確認中…','[CLAUDE] コード品質を分析中…','[CLAUDE] 重複処理を検出中…','[CLAUDE] セキュリティリスクをスキャン中…','[CLAUDE] 改善ポイントを整理中…','[CLAUDE] 依存関係をチェック中…'];
  var QUEUE_LINES=['[QUEUE] 解析待ち…','[QUEUE] 順番待ち（1件ずつ処理中）…','[QUEUE] まもなく開始します…'], _qi=0;

  function hexA(hex,a){ var n=parseInt(hex.slice(1),16); return 'rgba('+((n>>16)&255)+','+((n>>8)&255)+','+(n&255)+','+a+')'; }
  function countTo(el,target,dur){ if(reduce){el.textContent=target;return;} var s=performance.now(); requestAnimationFrame(function tick(now){ var p=Math.min(1,(now-s)/dur); el.textContent=Math.round(target*(1-Math.pow(1-p,3))); if(p<1)requestAnimationFrame(tick); }); }
  function addLog(t){ if(!runConsole)return; var l=document.createElement('div'); l.className='cl'; l.textContent=t; runConsole.appendChild(l); while(runConsole.children.length>5)runConsole.removeChild(runConsole.firstChild); }
  function setActiveStep(idx){ steps.forEach(function(s,i){ s.classList.remove('active','done'); if(i<idx)s.classList.add('done'); else if(i===idx)s.classList.add('active'); }); for(var i=0;i<3;i++){ if(chaser[i]){ i<idx?chaser[i].classList.add('on'):chaser[i].classList.remove('on'); } } if(chaser[idx])chaser[idx].classList.add('on'); }
  function setStep(step){ if(step===_curStep||!STEPS[step])return; if(_anTimer){clearInterval(_anTimer);_anTimer=null;} _curStep=step; var c=STEPS[step]; addLog(c.log); _targetPct=c.pct; setActiveStep(c.step); if(step==='analyzing'){ var i=0; _anTimer=setInterval(function(){ i=(i+1)%ANALYZING_LINES.length; addLog(ANALYZING_LINES[i]); },3200); } }
  function startBar(){ if(_barTimer)return; _barTimer=setInterval(function(){ var cap=Math.min(99,_targetPct+7); if(_animPct<cap)_animPct+=Math.max(.12,(cap-_animPct)*0.05); if(runBar)runBar.style.width=_animPct+'%'; if(runPct)runPct.textContent=Math.round(_animPct)+'%'; },90); }
  function stopBar(){ if(_barTimer){clearInterval(_barTimer);_barTimer=null;} }
  function finishBar(){ stopBar(); _animPct=100; if(runBar)runBar.style.width='100%'; if(runPct)runPct.textContent='100%'; }

  function reset(){
    stopBar(); if(_anTimer){clearInterval(_anTimer);_anTimer=null;}
    _curStep=''; _animPct=0; _targetPct=5;
    if(runConsole)runConsole.innerHTML=''; if(runBar)runBar.style.width='0'; if(runPct)runPct.textContent='0%';
    var rs=document.getElementById('report-slot'); if(rs)rs.innerHTML='';
    var mc0=document.getElementById('memoryCard'), ea0=document.getElementById('exitActions'), eor0=document.getElementById('endOfReview');
    if(mc0){ mc0.style.display='none'; mc0.classList.remove('memory-land'); } if(ea0)ea0.style.display='none'; if(eor0)eor0.classList.remove('show');
    run.classList.remove('open'); proof.classList.remove('show'); consoleEl.classList.remove('analyzing');
    hero.classList.remove('land'); rest.classList.remove('show');
    steps.forEach(function(s){s.classList.remove('active','done');});
    chaser.forEach(function(w){w.classList.remove('on');});
    document.body.classList.remove('ws-analyzing','ws-processing');
    ringfill.style.transition='none'; ringfill.setAttribute('stroke-dashoffset','603');
    scoreEl.textContent='0'; go.disabled=false; go.textContent='Analyze';
  }

  function analyze(){
    if(_busy) return; _busy=true;
    reset(); _busy=true;
    go.disabled=true; go.textContent='Analyzing…';
    consoleEl.classList.add('analyzing'); run.classList.add('open'); document.body.classList.add('ws-analyzing','ws-processing');
    addLog('[INIT] CodeLens AI 起動中…'); startBar(); setStep('pending');

    var body=new FormData(); body.append('github_url', repo.value); body.append('_token', CSRF);
    fetch('{{ route('preview.analyze') }}', { method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}, body:body })
      .then(function(r){ return r.json(); })
      .then(function(d){ if(d.error){ throw new Error(d.error); } poll(d.status_url, d.id); })
      .catch(function(e){ fail(e.message); });
  }

  function poll(statusUrl, id){
    fetch(statusUrl, {headers:{'Accept':'application/json'}})
      .then(function(r){ return r.json(); })
      .then(function(s){
        if(s.progress_step){ setStep(s.progress_step); }
        else if(s.status==='pending'){ _qi=(_qi+1)%QUEUE_LINES.length; addLog(QUEUE_LINES[_qi]); }
        if(s.status==='complete'){ getResult(id); }
        else if(s.status==='failed'){ fail('解析に失敗しました'); }
        else { setTimeout(function(){ poll(statusUrl, id); }, 1800); }
      })
      .catch(function(){ setTimeout(function(){ poll(statusUrl, id); }, 2200); });
  }

  function getResult(id){
    if(_anTimer){clearInterval(_anTimer);_anTimer=null;}
    finishBar(); addLog('[DONE]  レビュー完了 ✓');
    document.body.classList.remove('ws-processing'); // 解析終了＝世界が戻る
    steps.forEach(function(st){st.classList.remove('active');st.classList.add('done');});
    chaser.slice(0,3).forEach(function(w){w.classList.add('on');});
    fetch('/preview/result/'+id, {headers:{'Accept':'application/json'}})
      .then(function(r){ return r.json(); })
      .then(function(d){ _busy=false; finish(d); setTimeout(function(){ loadReport(id); }, reduce?0:1400); }) /* ③ 62着地→0.5秒の間→Findingsが次の章として現れる */
      .catch(function(){ fail('結果の取得に失敗しました'); });
  }

  function finish(d){
    repoNameEl.textContent = d.repo || (repo.value.split('/').filter(Boolean).pop()||'repository');
    gradeEl.textContent=d.grade; gradeEl.style.color=d.color; scoreEl.style.color=d.color; ringfill.setAttribute('stroke',d.color);
    var hs=document.getElementById('heroStars');
    if(hs){ var n=Math.max(0,Math.min(5,Math.round(d.score/20))); hs.style.color=d.color; hs.textContent=Array(n+1).join('★')+Array(6-n).join('☆'); }
    hero.style.setProperty('--glow',hexA(d.color,.22)); gradeEl.style.textShadow='0 0 20px '+hexA(d.color,.55);
    proof.classList.add('show'); hero.classList.remove('land'); void hero.offsetWidth; hero.classList.add('land');
    var off=603*(1-d.score/100);
    if(reduce){ ringfill.setAttribute('stroke-dashoffset',off); scoreEl.textContent=d.score; chaser[3].classList.add('on'); rest.classList.add('show'); }
    else{
      requestAnimationFrame(function(){ ringfill.style.transition='stroke-dashoffset 1.2s cubic-bezier(.3,0,.2,1)'; ringfill.setAttribute('stroke-dashoffset',off); });
      countTo(scoreEl,d.score,1200);
      setTimeout(function(){chaser[3].classList.add('on');},1200);
      setTimeout(function(){rest.classList.add('show');},1050);
    }
    go.disabled=false; go.textContent='Analyze';
    var insightBox=document.getElementById('insightBox'), insightEl=document.getElementById('insightText');
    if(insightBox){
      if(d.insight){
        var ix=d.insight.indexOf('。');
        if(ix>0 && ix<d.insight.length-1){ insightEl.innerHTML=''; var b=document.createElement('strong'); b.textContent=d.insight.slice(0,ix+1); insightEl.appendChild(b); var restSpan=document.createElement('span'); restSpan.className='insight-rest'; restSpan.textContent=d.insight.slice(ix+1).replace(/^\s+/,''); insightEl.appendChild(restSpan); } /* 一文目＝リード → 息継ぎ → 残り（AI文は無改変・表示だけで2文に）*/
        else { insightEl.textContent=d.insight; }
        insightBox.style.display='';
      } else { insightBox.style.display='none'; }
    }
    var mc=document.getElementById('memoryCard'), ea=document.getElementById('exitActions'), eor=document.getElementById('endOfReview');
    var stamp=document.querySelector('#memoryCard .txt span');
    if(stamp) stamp.innerHTML='Memory Entry '+d.entry+' · 記録済み。次のレビューは、この履歴の上に積まれる。';
    // Memory Card ＝ 一冊の締め（発光しながら出す）＋ End of Review 幕引き
    setTimeout(function(){ if(mc){ mc.style.display=''; mc.classList.remove('memory-land'); void mc.offsetWidth; mc.classList.add('memory-land'); } if(ea)ea.style.display=''; }, reduce?0:1100);
    setTimeout(function(){ if(eor)eor.classList.add('show'); }, reduce?0:2000);
  }

  function loadReport(id){
    var slot=document.getElementById('report-slot'); if(!slot) return;
    fetch('/preview/report/'+id, {headers:{'Accept':'text/html'}})
      .then(function(r){ return r.text(); })
      .then(function(html){
        slot.innerHTML=html; slot.classList.remove('rin'); void slot.offsetWidth; slot.classList.add('rin');
        // innerHTML では <script> が走らないので、3リングを手動で起動（0/0/0対策）
        slot.querySelectorAll('.ring-arc').forEach(function(a){ a.setAttribute('stroke-dashoffset', a.getAttribute('data-final-offset')||'0'); });
        slot.querySelectorAll('.ring-num').forEach(function(n){ var t=parseInt(n.getAttribute('data-target')||'0',10), s=performance.now(); requestAnimationFrame(function tk(now){ var p=Math.min(1,(now-s)/900); n.textContent=Math.round(t*(1-Math.pow(1-p,3))); if(p<1)requestAnimationFrame(tk); }); });
        // ① 概要クランプ：3行を超えたら「続きを読む」
        var st=slot.querySelector('.summary-text');
        if(st && st.scrollHeight > st.clientHeight + 4){
          var more=document.createElement('span'); more.className='summary-more'; more.textContent='続きを読む ▾';
          st.parentNode.appendChild(more);
          more.addEventListener('click', function(){ var ex=st.classList.toggle('expanded'); more.textContent= ex ? '折りたたむ ▴' : '続きを読む ▾'; });
        }
      })
      .catch(function(){});
  }
  function fail(msg){ _busy=false; reset(); alert(msg||'エラー'); }

  go.addEventListener('click',analyze);
  repo.addEventListener('keydown',function(e){ if(e.key==='Enter')analyze(); });
  again.addEventListener('click',function(){ reset(); repo.focus(); });
  Array.prototype.forEach.call(document.querySelectorAll('.chip'),function(c){
    c.addEventListener('click',function(){ repo.value=c.getAttribute('data-repo'); reset(); repo.focus(); });
  });
})();
</script>
@endsection
