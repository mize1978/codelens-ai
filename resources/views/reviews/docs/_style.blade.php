{{-- Docs 共通スタイル：索引とサブページ(architecture 等)が継ぐ「リファレンスの文法」。
     配色は編集部の統一パレット(var(--cyan) / rgba(204,232,255)) に合わせ、
     body は mono 中心の「冷たさ・静けさ」を維持する。masthead は _editorial(ed-*) に統一。--}}
<style>
  /* Workspace と同じ「解析デスク」の空間を Docs にも持ち込む。 */
  body{ min-height:100vh;
    background:radial-gradient(ellipse at 65% 47%,#202b7259,transparent 55%),
      radial-gradient(ellipse at 30% 100%,#23388b66,transparent 60%),#060917!important;
    background-attachment:fixed!important; }
  body::before{ content:''; position:fixed; inset:80px 0 0; z-index:-2; pointer-events:none;
    background-image:linear-gradient(#1d39751f 1px,transparent 1px),linear-gradient(90deg,#1d39751f 1px,transparent 1px);
    background-size:70px 70px; mask-image:linear-gradient(transparent,#000); }
  #particle-canvas{ display:none!important; }
  .jr-sky{ position:fixed; inset:80px 0 0; z-index:-1; pointer-events:none;
    background-image:radial-gradient(circle,#86c9ff99 1px,transparent 2px); background-size:127px 109px;
    mask-image:linear-gradient(#000,transparent); }

  /* ヒーローと同じヘッダー。既存の導線とリンクは変えない。 */
  .app-header{ height:80px; padding:0 5vw; background:#080b1c!important;
    border-bottom:1px solid #263457!important; backdrop-filter:none!important; }
  .logo{ animation:none!important; }
  .logo-name{ font-size:1.42rem!important; }
  .nav{ gap:28px!important; top:auto!important; }
  .nav a{ font-size:.69rem!important; letter-spacing:.18em!important; }
  .repo-door{ width:auto!important; height:52px!important; border:none!important; border-radius:0!important;
    background:none!important; box-shadow:none!important; gap:9px!important; padding:0!important; }
  .repo-door img{ width:auto!important; height:46px!important; }
  .repo-door-tip{ position:static!important; display:inline!important; opacity:1!important; transform:none!important;
    white-space:nowrap; font:11px/1 system-ui,sans-serif!important; color:#ecf2ff!important; background:none!important;
    border:none!important; padding:0!important; pointer-events:auto!important; }
  .repo-door-tip::after{ content:' ↗'; }
  .status-pill{ font-size:.64rem!important; }

  .doc-wrap{ max-width:820px; margin:0 auto; padding:52px 24px 96px;
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif; }

  .ref-back{ display:inline-block; font-size:.66rem; letter-spacing:.14em; text-transform:uppercase;
    color:rgba(0,205,255,.6); text-decoration:none; margin-bottom:36px; transition:color .2s ease; }
  .ref-back:hover{ color:var(--cyan); }

  {{-- サブページ masthead：編集部の見出し文法(eyebrow=cyan / title=gradient)に合わせる --}}
  .doc-head{ margin-bottom:28px; }
  .doc-kicker{ font-size:.62rem; font-weight:700; letter-spacing:.38em; text-transform:uppercase; color:var(--cyan); opacity:.85; margin-bottom:15px; }
  .doc-title{ font-size:clamp(26px,4.6vw,38px); font-weight:700; letter-spacing:-.3px; line-height:1.18; margin:0 0 13px;
    background:linear-gradient(135deg,#ffffff,#8fc7ff); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
  .doc-lead{ font-size:.82rem; line-height:1.9; color:rgba(204,232,255,.55); margin:0 0 9px; }
  .doc-sub{ font-size:.82rem; line-height:1.75; color:rgba(204,232,255,.5); margin:0; }

  .doc-rule{ height:1px; background:linear-gradient(90deg,rgba(143,193,255,.48),rgba(0,200,255,.1),transparent); margin:0; }

  {{-- 索引：Articles と違い title と説明を1行に並べる（探す用・静か） --}}
  .doc-index{ display:flex; flex-direction:column; }
  .doc-item{ position:relative; display:flex; align-items:baseline; gap:16px; text-decoration:none;
    padding:23px 36px 23px 16px; border-bottom:1px solid rgba(139,172,255,.16); transition:transform .2s ease, padding-left .2s ease, background .2s ease; }
  .doc-item:last-child{ border-bottom:none; }
  .doc-item::after{ content:'→'; position:absolute; right:8px; top:24px; font-size:.85rem;
    color:var(--cyan); opacity:0; transform:translateX(-6px); transition:opacity .2s ease, transform .2s ease; }
  .doc-item:hover{ transform:translateX(2px); padding-left:20px; background:rgba(92,119,214,.08); }
  .doc-item:hover::after{ opacity:1; transform:translateX(0); }
  .doc-item .di-h{ flex:0 0 auto; font-size:1rem; font-weight:600; color:#eaf5ff; }
  .doc-item:hover .di-h{ color:#fff; }
  .doc-item .di-sub{ font-size:.82rem; color:rgba(204,232,255,.55); }

  {{-- 締めを書かない代わりの一行（本ではなくリファレンス） --}}
  .doc-foot{ margin-top:34px; font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:.5px; color:rgba(204,232,255,.32); line-height:1.9; }

  .ref-body{ font-family:'JetBrains Mono',monospace; }
  .ref-oneliner{ font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif;
    font-size:18px; line-height:1.7; color:#eaf5ff; font-weight:500; margin:34px 0 4px; }
  .ref-h2{ font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:2px; text-transform:uppercase; color:rgba(0,205,255,.7); margin:38px 0 14px; }
  .ref-fact{ font-family:'JetBrains Mono',monospace; font-size:14px; line-height:1.75; color:rgba(204,232,255,.75); margin:10px 0; }

  .ref-flow{ display:flex; flex-direction:column; align-items:flex-start; margin:22px 0; }
  .fl-step{ font-family:'JetBrains Mono',monospace; font-size:16px; color:#eaf5ff; padding:9px 18px;
    border:1px solid rgba(0,200,255,.16); border-radius:8px; background:rgba(0,200,255,.03); }
  .fl-arrow{ color:rgba(204,232,255,.35); font-size:14px; margin:5px 0 5px 20px; }
  {{-- Architecture は「順番(↓)」でなく「構造(│)」：細い縦のコネクタ線 --}}
  .fl-line{ width:1px; height:22px; background:rgba(0,200,255,.24); margin-left:24px; }

  .ref-list{ list-style:none; padding:0; margin:14px 0; }
  .ref-list li{ font-family:'JetBrains Mono',monospace; font-size:14px; line-height:1.5; color:rgba(204,232,255,.75);
    padding:8px 0 8px 20px; position:relative; border-bottom:1px solid rgba(0,200,255,.06); }
  .ref-list li::before{ content:'·'; position:absolute; left:5px; color:var(--cyan); }

  .ref-steps{ list-style:none; padding:0; margin:14px 0; counter-reset:s; }
  .ref-steps li{ font-family:'JetBrains Mono',monospace; font-size:14px; line-height:1.5; color:rgba(204,232,255,.75);
    padding:10px 0 10px 36px; position:relative; border-bottom:1px solid rgba(0,200,255,.06); counter-increment:s; }
  .ref-steps li::before{ content:counter(s); position:absolute; left:2px; top:9px; font-size:11px; color:var(--cyan);
    border:1px solid rgba(0,200,255,.4); border-radius:4px; width:19px; height:19px; display:flex; align-items:center; justify-content:center; }

  .ref-score{ margin:20px 0; }
  .sc-inputs{ display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
  .sc-chip{ font-family:'JetBrains Mono',monospace; font-size:14px; color:rgba(204,232,255,.75); padding:8px 14px;
    border:1px solid rgba(0,200,255,.16); border-radius:8px; background:rgba(0,200,255,.03); }
  .sc-arrow{ font-family:'JetBrains Mono',monospace; color:rgba(204,232,255,.35); font-size:13px; margin:0 0 10px 6px; }
  .sc-out{ font-family:'JetBrains Mono',monospace; font-size:15px; color:#eaf5ff; padding:9px 16px;
    border:1px solid rgba(0,200,255,.35); border-radius:8px; display:inline-block; }

  .ref-api{ margin:16px 0; }
  .ep{ display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid rgba(0,200,255,.08); }
  .ep .m{ font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:700; letter-spacing:1px; padding:3px 9px; border-radius:5px; min-width:46px; text-align:center; }
  .ep .m.get{ color:var(--green); background:rgba(0,255,136,.1); }
  .ep .m.post{ color:#d6a531; background:rgba(214,165,49,.1); }
  .ep .path{ font-family:'JetBrains Mono',monospace; font-size:14px; color:#eaf5ff; }

  .ref-last{ margin-top:52px; padding-top:22px; border-top:1px solid rgba(0,200,255,.09);
    font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:.5px; color:rgba(204,232,255,.32); line-height:1.9; }

  @media(max-width:750px){
    .app-header{ height:70px; padding:0 20px; }
    .nav,.status-pill{ display:none!important; }
    .repo-door{ margin-left:auto!important; }
    .repo-door-tip{ font-size:10px!important; }
    .doc-wrap{ padding:36px 20px 72px; }
    .doc-item{ align-items:flex-start; flex-direction:column; gap:6px; }
  }
</style>
