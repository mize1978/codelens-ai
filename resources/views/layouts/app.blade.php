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
    .logo {
      font-size: 1.4rem; font-weight: 900;
      background: linear-gradient(135deg, var(--cyan), var(--blue));
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      letter-spacing: -0.02em;
    }
    .logo span { -webkit-text-fill-color: var(--purple); }
    .nav { display: flex; gap: 20px; align-items: center; }
    .nav a { font-size: 0.73rem; color: var(--text-dim); letter-spacing: 0.12em; text-transform: uppercase; transition: color 0.2s; }
    .nav a:hover { color: var(--cyan); }
    .status-pill {
      display: flex; align-items: center; gap: 6px;
      font-size: 0.68rem; color: var(--text-dim);
      border: 1px solid var(--border); border-radius: 20px; padding: 4px 10px;
    }
    .dot-online { width: 6px; height: 6px; border-radius: 50%; background: var(--green); box-shadow: 0 0 6px var(--green); }

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
  </style>
  @yield('head')
</head>
<body>
  <header class="app-header">
    <a href="{{ route('reviews.index') }}" class="logo">CodeLens<span>AI</span></a>
    <nav class="nav">
      <a href="{{ route('ranking') }}">ランキング</a>
    </nav>
    <div class="status-pill">
      <span class="dot-online"></span>
      AI ONLINE
    </div>
  </header>

  <main>
    @yield('content')
  </main>
</body>
</html>
