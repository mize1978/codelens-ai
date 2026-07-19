<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', 'CodeLens AI — AIコードレビュー')</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg:      #04040f;
      --bg2:     #080820;
      --bg3:     #0d0d28;
      --border:  rgba(0,200,255,0.18);
      --border-hi: rgba(0,200,255,0.5);
      --cyan:    #00ccff;
      --blue:    #4488ff;
      --purple:  #9944ff;
      --green:   #00ff88;
      --red:     #ff4466;
      --yellow:  #ffcc00;
      --text:    #cce8ff;
      --text-dim: rgba(180,220,255,0.5);
      --text-mute: rgba(140,180,220,0.3);
      --glow-c:  0 0 20px rgba(0,200,255,0.35), 0 0 40px rgba(0,200,255,0.1);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg); color: var(--text);
      font-family: 'JetBrains Mono', monospace;
      min-height: 100vh;
    }
    body::after {
      content: ''; position: fixed; inset: 0; pointer-events: none; z-index: 9999;
      background: repeating-linear-gradient(0deg, rgba(0,0,0,0) 0px, rgba(0,0,0,0) 2px, rgba(0,0,0,0.025) 2px, rgba(0,0,0,0.025) 4px);
    }
    a { color: inherit; text-decoration: none; }

    /* Header */
    .app-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 24px;
      border-bottom: 1px solid var(--border);
      background: rgba(4,4,20,0.9); backdrop-filter: blur(12px);
      position: sticky; top: 0; z-index: 100;
    }
    .logo-block { display: flex; flex-direction: column; gap: 1px; }
    .logo {
      display: flex; align-items: center; gap: 9px;
      text-decoration: none;
      animation: logoBreathe 6s ease-in-out infinite;
    }
    .logo-name {
      font-size: 1.25rem; font-weight: 900; letter-spacing: -0.02em;
      background: linear-gradient(135deg, var(--cyan), var(--blue));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      line-height: 1.1;
    }
    .logo-name span { -webkit-text-fill-color: var(--purple); }
    .logo-craft {
      font-size: 0.55rem; color: var(--text-mute); letter-spacing: 0.08em;
      -webkit-text-fill-color: var(--text-mute);
      font-weight: 400; position: relative; display: inline-block;
      transition: color 0.2s;
    }
    .logo-craft::after {
      content: ''; position: absolute; bottom: -1px; left: 0;
      width: 0; height: 1px;
      background: var(--cyan);
      transition: width 0.3s ease-out;
    }
    .craft-arrow {
      font-size: 0.8em; opacity: 0.22;
      transition: opacity 0.2s;
      display: inline-block; vertical-align: middle;
    }
    .logo-craft:hover .craft-arrow { opacity: 0.9; }
    .logo-craft:hover { color: rgba(0,200,255,0.7); -webkit-text-fill-color: rgba(0,200,255,0.7); }
    .logo-craft:hover::after { width: 100%; }
    .logo-icon {
      width: 28px; height: 28px; border-radius: 7px;
      object-fit: cover;
      filter: drop-shadow(0 0 7px rgba(0,150,255,0.5)) drop-shadow(0 0 2px rgba(120,80,255,0.3));
    }
    .nav { display: flex; gap: 20px; align-items: center; }
    .nav a { font-size: 0.73rem; color: var(--text-dim); letter-spacing: 0.12em; text-transform: uppercase; transition: color 0.2s; }
    .nav a:hover { color: var(--cyan); }
    /* Status pill */
    .status-pill {
      display: flex; align-items: center; gap: 6px;
      font-size: 0.68rem; color: var(--text-dim);
      border: 1px solid var(--border); border-radius: 20px; padding: 4px 10px;
      cursor: default; position: relative; transition: border-color 0.3s, color 0.3s;
      user-select: none;
    }
    .status-pill[data-state="active"]    { border-color: rgba(0,255,136,0.35); color: var(--green); }
    .status-pill[data-state="reviewing"] { border-color: rgba(68,136,255,0.5);  color: var(--blue); }
    .status-pill[data-state="thinking"]  { border-color: rgba(153,68,255,0.5);  color: var(--purple); }
    .status-pill[data-state="complete"]  { border-color: rgba(255,204,0,0.5);   color: var(--yellow); }

    .status-dot {
      width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0;
      transition: background 0.3s, box-shadow 0.3s;
    }
    .status-pill[data-state="active"]    .status-dot { background: var(--green);  box-shadow: 0 0 0 0 rgba(0,255,136,0.6);  animation: pulse-active 2.4s ease-in-out infinite; }
    .status-pill[data-state="reviewing"] .status-dot { background: var(--blue);   animation: pulse-reviewing 0.9s ease-in-out infinite; }
    .status-pill[data-state="thinking"]  .status-dot { background: var(--purple); animation: pulse-thinking 0.6s ease-in-out infinite; }
    .status-pill[data-state="complete"]  .status-dot { background: var(--yellow); animation: none; box-shadow: 0 0 8px rgba(255,204,0,0.8); }

    @keyframes pulse-active    { 0%,100%{box-shadow:0 0 0 0 rgba(0,255,136,0.6)}  50%{box-shadow:0 0 0 4px rgba(0,255,136,0)} }
    @keyframes pulse-reviewing { 0%,100%{opacity:1} 50%{opacity:0.3} }
    @keyframes pulse-thinking  { 0%,100%{opacity:1; transform:scale(1)} 50%{opacity:0.5; transform:scale(1.6)} }

    /* ACTIVE: 呼吸グロー */
    @keyframes breathe-active {
      0%,100% { box-shadow: 0 0 0 0 rgba(0,255,136,0); }
      50%      { box-shadow: 0 0 8px 2px rgba(0,255,136,0.25); }
    }
    .status-pill[data-state="active"] { animation: breathe-active 3s ease-in-out infinite; }

    /* REVIEWING: 青いラインが流れる */
    @keyframes shimmer-review {
      0%   { background-position: -200% center; }
      100% { background-position:  200% center; }
    }
    .status-pill[data-state="reviewing"] {
      background: linear-gradient(90deg, transparent 0%, rgba(68,136,255,0.15) 40%, rgba(0,200,255,0.2) 50%, rgba(68,136,255,0.15) 60%, transparent 100%);
      background-size: 200% auto;
      animation: shimmer-review 1.4s linear infinite;
    }

    /* COMPLETE: 金色フラッシュ */
    @keyframes flash-complete {
      0%   { box-shadow: 0 0 0 0 rgba(255,204,0,0); }
      30%  { box-shadow: 0 0 16px 4px rgba(255,204,0,0.6); }
      100% { box-shadow: 0 0 0 0 rgba(255,204,0,0); }
    }
    .status-pill[data-state="complete"] { animation: flash-complete 0.8s ease-out forwards; }

    /* HUD tooltip */
    .status-hud {
      display: none; position: absolute; top: calc(100% + 8px); right: 0;
      background: rgba(4,4,20,0.96); border: 1px solid var(--border-hi);
      border-radius: 8px; padding: 12px 14px; min-width: 180px;
      backdrop-filter: blur(12px); z-index: 999;
      font-size: 0.65rem; letter-spacing: 0.06em;
      box-shadow: 0 8px 32px rgba(0,200,255,0.1);
    }
    .status-pill:hover .status-hud { display: block; }
    .hud-title {
      font-size: 0.58rem; letter-spacing: 0.2em; text-transform: uppercase;
      color: var(--cyan); margin-bottom: 8px; font-weight: 700;
    }
    .hud-row {
      display: flex; justify-content: space-between; gap: 16px;
      color: var(--text-dim); margin-bottom: 4px;
    }
    .hud-row span:last-child { color: var(--text); font-weight: 700; }

    /* Panel */
    .panel {
      background: var(--bg3); border: 1px solid var(--border); border-radius: 10px; overflow: hidden;
    }
    .panel-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 9px 14px; border-bottom: 1px solid var(--border);
      background: var(--bg2); font-size: 0.65rem; font-weight: 700;
      letter-spacing: 0.18em; color: var(--text-dim); text-transform: uppercase;
    }
    .wdots { display: flex; gap: 5px; }
    .wd { width: 8px; height: 8px; border-radius: 50%; }

    /* Score ring */
    .score-ring-wrap { position: relative; width: 100px; height: 100px; }
    .score-ring { width: 100%; height: 100%; transform: rotate(-90deg); }
    .ring-bg  { fill: none; stroke: rgba(255,255,255,0.06); stroke-width: 10; }
    .ring-fill { fill: none; stroke-width: 10; stroke-linecap: round; stroke-dasharray: 283; stroke-dashoffset: 283; transition: stroke-dashoffset 1.5s ease; }
    .ring-fill.green  { stroke: var(--green);  filter: drop-shadow(0 0 6px rgba(0,255,136,0.6)); }
    .ring-fill.blue   { stroke: var(--blue);   filter: drop-shadow(0 0 6px rgba(68,136,255,0.6)); }
    .ring-fill.yellow { stroke: var(--yellow); filter: drop-shadow(0 0 6px rgba(255,204,0,0.6)); }
    .ring-fill.red    { stroke: var(--red);    filter: drop-shadow(0 0 6px rgba(255,68,102,0.6)); }
    .ring-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .ring-pct { font-size: 1.4rem; font-weight: 900; }
    .ring-label { font-size: 0.55rem; letter-spacing: 0.1em; }

    /* Bar */
    .bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
    .bar-name { font-size: 0.68rem; color: var(--text-dim); min-width: 72px; }
    .bar-track { flex: 1; height: 5px; background: rgba(255,255,255,0.06); border-radius: 3px; overflow: hidden; }
    .bar-fill { height: 100%; width: 0; border-radius: 3px; background: linear-gradient(90deg, var(--cyan), var(--blue)); transition: width 1.5s ease; }
    .bar-val { font-size: 0.68rem; color: var(--cyan); min-width: 32px; text-align: right; }

    /* Buttons */
    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: 8px;
      padding: 12px 24px; border-radius: 6px; font-family: inherit; font-size: 0.82rem;
      font-weight: 700; letter-spacing: 0.08em; cursor: pointer; transition: all 0.25s;
      border: none; text-decoration: none;
    }
    .btn-primary {
      background: linear-gradient(135deg, rgba(0,200,255,0.2), rgba(68,136,255,0.2));
      border: 1px solid var(--border-hi); color: var(--cyan);
    }
    .btn-primary:hover { box-shadow: var(--glow-c); transform: translateY(-1px); background: linear-gradient(135deg, rgba(0,200,255,0.35), rgba(68,136,255,0.35)); }

    /* Form */
    .input-field {
      width: 100%; background: var(--bg); border: 1px solid var(--border); border-radius: 6px;
      padding: 12px 16px; color: var(--text); font-family: inherit; font-size: 0.85rem;
      outline: none; transition: border-color 0.2s;
    }
    .input-field::placeholder { color: var(--text-mute); }
    .input-field:focus { border-color: var(--cyan); box-shadow: 0 0 0 2px rgba(0,200,255,0.1); }

    /* Badge */
    .badge {
      display: inline-flex; align-items: center; gap: 4px;
      font-size: 0.62rem; padding: 2px 8px; border-radius: 4px;
      font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
    }
    .badge-green  { background: rgba(0,255,136,0.12); color: var(--green); border: 1px solid rgba(0,255,136,0.3); }
    .badge-blue   { background: rgba(68,136,255,0.12); color: var(--blue); border: 1px solid rgba(68,136,255,0.3); }
    .badge-yellow { background: rgba(255,204,0,0.12); color: var(--yellow); border: 1px solid rgba(255,204,0,0.3); }
    .badge-red    { background: rgba(255,68,102,0.12); color: var(--red); border: 1px solid rgba(255,68,102,0.3); }

    /* Issue card */
    .issue-card {
      border-left: 3px solid; padding: 10px 12px; border-radius: 0 6px 6px 0;
      background: rgba(255,255,255,0.03); margin-bottom: 8px;
    }
    .issue-card.high   { border-color: var(--red); }
    .issue-card.medium { border-color: var(--yellow); }
    .issue-card.low    { border-color: var(--blue); }
    .issue-title { font-size: 0.78rem; font-weight: 700; margin-bottom: 4px; }
    .issue-desc  { font-size: 0.72rem; color: var(--text-dim); line-height: 1.5; }

    /* List item */
    .check-item { display: flex; gap: 8px; font-size: 0.75rem; color: var(--text-dim); margin-bottom: 6px; line-height: 1.5; }
    .check-item .check { color: var(--green); flex-shrink: 0; }
    .check-item .warn  { color: var(--yellow); flex-shrink: 0; }

    /* Section title */
    .section-title { font-size: 0.62rem; color: var(--text-mute); letter-spacing: 0.2em; text-transform: uppercase; margin-bottom: 12px; }

    /* Misc */
    .hidden { display: none !important; }
    .mt-4 { margin-top: 16px; }
    .text-dim { color: var(--text-dim); }
    .text-mute { color: var(--text-mute); font-size: 0.72rem; }

    /* AI CODE REVIEW SYSTEM — slow glow pulse */
    @keyframes textGlow {
      0%, 100% { opacity: 0.3; text-shadow: none; }
      50%       { opacity: 0.9; text-shadow: 0 0 12px rgba(0,200,255,0.6), 0 0 24px rgba(0,200,255,0.2); }
    }
    .ai-system-label {
      font-size: 0.7rem; letter-spacing: 0.25em;
      color: rgba(140,200,255,0.9); margin-bottom: 12px;
      animation: textGlow 4s ease-in-out infinite;
    }

    /* Hero logo (index page) */
    .hero-logo-text {
      font-size: 2.4rem; font-weight: 500;
      background: linear-gradient(135deg, var(--cyan), var(--blue), var(--purple));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      margin-bottom: 12px; letter-spacing: -0.02em;
      filter: drop-shadow(0 0 10px rgba(80,100,255,0.45)) drop-shadow(0 0 20px rgba(40,80,255,0.2));
    }

    /* Header logo — subtle blue breathing */
    @keyframes logoBreathe {
      0%, 100% { filter: brightness(1)   drop-shadow(0 0 0px transparent); }
      50%       { filter: brightness(1.1) drop-shadow(0 0 5px rgba(0,160,255,0.25)); }
    }

    /* Logo spin — during review only */
    @keyframes logoSpin {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }
    .logo-icon.spinning {
      animation: logoSpin 3s linear infinite !important;
      filter: drop-shadow(0 0 6px rgba(0,200,255,0.5));
    }

    /* Button shimmer */
    @keyframes btnShimmer {
      0%   { background-position: -200% center; }
      100% { background-position:  200% center; }
    }
    .btn-reviewing {
      background: linear-gradient(90deg, rgba(0,150,255,0.15) 0%, rgba(0,200,255,0.35) 40%, rgba(68,136,255,0.35) 60%, rgba(0,150,255,0.15) 100%) !important;
      background-size: 200% auto !important;
      animation: btnShimmer 1.6s linear infinite !important;
      color: var(--cyan) !important;
      cursor: default !important;
      pointer-events: none;
    }

    /* Logo radar wrap */
    .logo-icon-wrap {
      position: relative; display: inline-flex;
      width: 28px; height: 28px; flex-shrink: 0;
    }
    .logo-icon-wrap::after {
      content: ''; position: absolute; inset: 0;
      border-radius: 7px;
      background: conic-gradient(from -90deg, rgba(0,200,255,0.45) 0deg, rgba(0,200,255,0.1) 50deg, transparent 50deg);
      opacity: 0; pointer-events: none;
    }
    .logo-icon-wrap.radar-active::after {
      animation: radarSweep 0.9s linear forwards;
    }
    @keyframes radarSweep {
      0%   { transform: rotate(0deg);   opacity: 1; }
      85%  { transform: rotate(360deg); opacity: 0.7; }
      100% { transform: rotate(400deg); opacity: 0; }
    }
  </style>
  @yield('head')
</head>
<body>
  <canvas id="particle-canvas" style="position:fixed;inset:0;z-index:0;pointer-events:none"></canvas>
  <header class="app-header">
    <div class="logo-block">
      <a href="{{ route('reviews.index') }}" class="logo">
        <span class="logo-icon-wrap">
          <img src="/images/devinsight-logo.png" class="logo-icon" alt="logo">
        </span>
        <span class="logo-name">CodeLens<span>AI</span></span>
      </a>
      <a href="https://github.com/mize1978" target="_blank" rel="noopener" class="logo-craft">crafted by Mize <span class="craft-arrow">↗</span></a>
    </div>
    <nav class="nav">
      <a href="{{ route('ranking') }}">ランキング</a>
    </nav>
    <div class="status-pill" id="cl-status" data-state="active">
      <span class="status-dot"></span>
      <span class="status-label">CodeLens ACTIVE</span>
      <div class="status-hud">
        <div class="hud-title">CodeLens Engine</div>
        <div class="hud-row"><span>Status</span><span id="hud-status">Online</span></div>
        <div class="hud-row"><span>Version</span><span>v0.4</span></div>
        <div class="hud-row"><span>Model</span><span>Claude Sonnet</span></div>
        <div class="hud-row"><span>Latency</span><span id="hud-latency">—</span></div>
        <div class="hud-row" id="hud-repo-row" style="display:none"><span>Repository</span><span id="hud-repo">—</span></div>
        <div class="hud-row" id="hud-files-row" style="display:none"><span>Files</span><span id="hud-files">—</span></div>
      </div>
    </div>
  </header>

  <main style="position:relative;z-index:1">
    @yield('content')
  </main>
<script>
// CodeLens Status Controller
window.CodeLensStatus = (function () {
  const pill     = document.getElementById('cl-status');
  const label    = pill.querySelector('.status-label');
  const hudStat  = document.getElementById('hud-status');
  const hudLat   = document.getElementById('hud-latency');
  const hudRepo  = document.getElementById('hud-repo');
  const hudFiles = document.getElementById('hud-files');
  let _startTime     = null;
  let _completeTimer = null;
  let _thinkTimer    = null;

  // Thinking... ドットアニメ
  let _thinkDot = 0;
  function startThinkDots() {
    _thinkDot = 0;
    _thinkTimer = setInterval(() => {
      _thinkDot = (_thinkDot + 1) % 4;
      label.textContent = '🟣 Thinking' + '.'.repeat(_thinkDot || 1);
    }, 400);
  }
  function stopThinkDots() {
    if (_thinkTimer) { clearInterval(_thinkTimer); _thinkTimer = null; }
  }

  // CodeLensくんセリフ更新（show.blade.php側のpm-textがあれば）
  function setMascotSpeech(text) {
    const el = document.getElementById('cl-speech');
    if (el) { el.style.opacity = 0; setTimeout(() => { el.textContent = text; el.style.opacity = 1; }, 200); }
  }

  function set(key, meta) {
    if (_completeTimer) { clearTimeout(_completeTimer); _completeTimer = null; }
    stopThinkDots();
    pill.dataset.state = key;
    if (hudStat) hudStat.textContent = { active:'Online', reviewing:'Reviewing', thinking:'Thinking', complete:'Complete' }[key] || 'Online';

    if (key === 'active') {
      label.textContent = '🟢 CodeLens ACTIVE';
      setMascotSpeech('なにか面白いコードはあるかな…');
    }
    if (key === 'reviewing') {
      label.textContent = '🔵 Reviewing...';
      _startTime = Date.now();
      hudLat && (hudLat.textContent = '—');
      setMascotSpeech('READMEも見てみよう！');
      if (meta?.repo)  { hudRepo.textContent = meta.repo;  document.getElementById('hud-repo-row').style.display = ''; }
    }
    if (key === 'thinking') {
      startThinkDots();
      setMascotSpeech('設計を確認中…');
    }
    if (key === 'complete') {
      label.textContent = '✨ Review Complete';
      setMascotSpeech('いいコード、見つけた！！');
      if (meta?.latency) hudLat && (hudLat.textContent = meta.latency + 's');
      if (meta?.files)   { hudFiles.textContent = meta.files; document.getElementById('hud-files-row').style.display = ''; }
      _completeTimer = setTimeout(() => set('active'), 800);
    }
  }

  return { set };
})();

// レーダースイープ — 6秒ごとに1回
(function radarLoop() {
  const wrap = document.querySelector('.logo-icon-wrap');
  if (wrap) {
    wrap.classList.add('radar-active');
    setTimeout(() => wrap.classList.remove('radar-active'), 900);
  }
  setTimeout(radarLoop, 6000);
})();

(function () {
  const canvas = document.getElementById('particle-canvas');
  const ctx = canvas.getContext('2d');
  let W, H, particles;
  const COUNT = 55;
  const CONNECT_DIST = 140;
  const MOUSE_RADIUS = 100;
  const mouse = { x: -9999, y: -9999 };

  function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }

  function mkParticle() {
    return {
      x: Math.random() * W,
      y: Math.random() * H,
      vx: (Math.random() - 0.5) * 0.25,
      vy: (Math.random() - 0.5) * 0.25,
      r: Math.random() * 1.4 + 0.6,
    };
  }

  function init() {
    resize();
    particles = Array.from({ length: COUNT }, mkParticle);
  }

  function draw() {
    ctx.clearRect(0, 0, W, H);

    // connect lines
    for (let i = 0; i < COUNT; i++) {
      for (let j = i + 1; j < COUNT; j++) {
        const a = particles[i], b = particles[j];
        const dx = a.x - b.x, dy = a.y - b.y;
        const d = Math.sqrt(dx * dx + dy * dy);
        if (d < CONNECT_DIST) {
          const alpha = (1 - d / CONNECT_DIST) * 0.18;
          ctx.beginPath();
          ctx.moveTo(a.x, a.y);
          ctx.lineTo(b.x, b.y);
          ctx.strokeStyle = `rgba(0,180,255,${alpha})`;
          ctx.lineWidth = 0.6;
          ctx.stroke();
        }
      }
    }

    // dots
    for (const p of particles) {
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(0,200,255,0.55)';
      ctx.fill();
    }
  }

  function update() {
    for (const p of particles) {
      // mouse repel — gentle
      const dx = p.x - mouse.x, dy = p.y - mouse.y;
      const d = Math.sqrt(dx * dx + dy * dy);
      if (d < MOUSE_RADIUS && d > 0) {
        const force = (MOUSE_RADIUS - d) / MOUSE_RADIUS * 0.012;
        p.vx += (dx / d) * force;
        p.vy += (dy / d) * force;
      }

      // dampen + move
      p.vx *= 0.98;
      p.vy *= 0.98;
      p.x += p.vx;
      p.y += p.vy;

      // wrap edges
      if (p.x < 0) p.x = W;
      if (p.x > W) p.x = 0;
      if (p.y < 0) p.y = H;
      if (p.y > H) p.y = 0;
    }
  }

  function loop() {
    update();
    draw();
    requestAnimationFrame(loop);
  }

  window.addEventListener('resize', () => { resize(); });
  window.addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
  window.addEventListener('mouseleave', () => { mouse.x = -9999; mouse.y = -9999; });

  init();
  loop();
})();
</script>

{{-- ===== CodeLensくん グローバル ===== --}}
<style>
/* トースト（右下） */
.mascot-toast {
  position: fixed; bottom: 28px; right: 24px; z-index: 9000;
  display: flex; align-items: flex-end; gap: 8px;
  opacity: 0; transform: translateY(12px);
  transition: opacity 0.35s, transform 0.35s; pointer-events: none;
}
.mascot-toast.show { opacity: 1; transform: translateY(0); }
.mascot-img {
  width: 64px; height: 64px; object-fit: contain; flex-shrink: 0;
  filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5));
}
.mascot-bubble {
  background: rgba(6,6,18,0.93); backdrop-filter: blur(12px);
  border: 1px solid rgba(0,200,255,0.18); border-radius: 12px 12px 12px 2px;
  padding: 10px 14px; max-width: 190px;
}
.mascot-quote { font-size: 0.8rem; color: #dde8f8; line-height: 1.55; margin-bottom: 4px; }
.mascot-name  { font-size: 0.55rem; color: var(--text-mute); letter-spacing: 0.12em; }

/* 完了オーバーレイ */
.mascot-overlay {
  position: fixed; inset: 0; z-index: 9999;
  display: flex; align-items: center; justify-content: center;
  background: rgba(0,0,0,0.75); backdrop-filter: blur(6px);
  opacity: 0; transition: opacity 0.4s;
}
.mascot-overlay.show { opacity: 1; }
.mc-card {
  text-align: center; padding: 44px 56px;
  background: rgba(5,5,16,0.97);
  border: 1px solid rgba(0,255,136,0.2); border-radius: 22px;
  box-shadow: 0 0 80px rgba(0,255,136,0.07);
  animation: mcCardIn 0.45s cubic-bezier(0.16,1,0.3,1);
}
@keyframes mcCardIn {
  from { transform: scale(0.88) translateY(10px); opacity: 0; }
  to   { transform: scale(1)    translateY(0);    opacity: 1; }
}
.mc-img    { width: 110px; height: 110px; object-fit: contain; margin-bottom: 16px;
             filter: drop-shadow(0 8px 24px rgba(0,0,0,0.5)); }
.mc-status { font-size: 0.58rem; font-weight: 800; letter-spacing: 0.28em; color: #00ff88; margin-bottom: 14px; }
.mc-quote  { font-size: 1.12rem; color: #eef2ff; line-height: 1.7; margin-bottom: 10px; }
.mc-name   { font-size: 0.6rem; color: var(--text-mute); letter-spacing: 0.16em; }
</style>

<script>
/* CodeLensくん トースト */
window.showMascot = function(imgSrc, quote, duration) {
  const el = document.createElement('div');
  el.className = 'mascot-toast';
  el.innerHTML = `<img class="mascot-img" src="${imgSrc}" alt="CodeLensくん">
    <div class="mascot-bubble">
      <div class="mascot-quote">「${quote}」</div>
      <div class="mascot-name">CodeLensくん</div>
    </div>`;
  document.body.appendChild(el);
  requestAnimationFrame(() => el.classList.add('show'));
  setTimeout(() => {
    el.classList.remove('show');
    setTimeout(() => el.remove(), 380);
  }, duration || 2000);
};

/* レビュー完了オーバーレイ */
window.showReviewComplete = function(cb) {
  const el = document.createElement('div');
  el.className = 'mascot-overlay';
  el.innerHTML = `<div class="mc-card">
    <img class="mc-img" src="/images/cl-win.png" alt="CodeLensくん">
    <div class="mc-status">✅ REVIEW COMPLETE</div>
    <div class="mc-quote">「いいコード、<br>見つけた…！」</div>
    <div class="mc-name">— CodeLensくん</div>
  </div>`;
  document.body.appendChild(el);
  requestAnimationFrame(() => el.classList.add('show'));
  setTimeout(() => {
    el.style.opacity = '0';
    setTimeout(() => { el.remove(); cb && cb(); }, 400);
  }, 1900);
};
</script>
</body>
</html>
