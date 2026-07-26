{{-- Docs 共通スタイル：Articles より冷たい「リファレンス」の文法（mono中心・余韻なし） --}}
<style>
  .jr-sky{ position:fixed; inset:0; z-index:-1;
    background:
      radial-gradient(1100px 620px at 50% -8%, rgba(56,120,190,.12), transparent 60%),
      radial-gradient(900px 520px at 85% 12%, rgba(120,80,190,.08), transparent 62%); }

  .doc-wrap{ max-width:720px; margin:0 auto; padding:64px 24px 96px;
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif; }

  .ref-back{ display:inline-block; font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:1px;
    color:#6b7488; text-decoration:none; margin-bottom:40px; transition:color .2s ease; }
  .ref-back:hover{ color:#8aa0b8; }

  .doc-head{ margin-bottom:26px; }
  .doc-kicker{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:3px; text-transform:uppercase; color:#6b7488; margin-bottom:15px; }
  .doc-title{ font-size:clamp(29px,5.3vw,42px); font-weight:700; color:#e8edf5; margin:0 0 13px; letter-spacing:-.5px; line-height:1.16; }
  .doc-lead{ font-size:15px; color:#8792a6; margin:0 0 9px; letter-spacing:.2px; }
  .doc-sub{ font-size:13.5px; line-height:1.75; color:#9aa4b8; margin:0; }

  .doc-rule{ height:1px; background:rgba(255,255,255,.09); margin:0; }

  {{-- 索引：Articles と違い title と説明を1行に並べる（探す用・冷たい） --}}
  .doc-index{ display:flex; flex-direction:column; }
  .doc-item{ position:relative; display:flex; align-items:baseline; gap:16px; text-decoration:none;
    padding:22px 30px 22px 6px; border-bottom:1px solid rgba(255,255,255,.06); transition:transform .2s ease, padding-left .2s ease; }
  .doc-item::after{ content:'→'; position:absolute; right:8px; top:24px; font-family:'JetBrains Mono',monospace;
    font-size:14px; color:#7d8aa0; opacity:0; transform:translateX(-6px); transition:opacity .2s ease, transform .2s ease; }
  .doc-item:hover{ transform:translateX(2px); padding-left:12px; }
  .doc-item:hover::after{ opacity:1; transform:translateX(0); }
  .doc-item .di-h{ flex:0 0 auto; font-size:16px; font-weight:600; color:#e8edf5; }
  .doc-item:hover .di-h{ color:#fff; }
  .doc-item .di-sub{ font-size:13px; color:#8792a6; }

  {{-- 締めを書かない代わりの一行（本ではなくリファレンス） --}}
  .doc-foot{ margin-top:34px; font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:.5px; color:#4d5568; line-height:1.9; }

  .ref-body{ font-family:'JetBrains Mono',monospace; }
  .ref-oneliner{ font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif;
    font-size:18px; line-height:1.7; color:#e8edf5; font-weight:500; margin:34px 0 4px; }
  .ref-h2{ font-family:'JetBrains Mono',monospace; font-size:12px; letter-spacing:2px; text-transform:uppercase; color:#6b7488; margin:38px 0 14px; }
  .ref-fact{ font-family:'JetBrains Mono',monospace; font-size:14px; line-height:1.75; color:#c2ccdb; margin:10px 0; }

  .ref-flow{ display:flex; flex-direction:column; align-items:flex-start; margin:22px 0; }
  .fl-step{ font-family:'JetBrains Mono',monospace; font-size:16px; color:#e8edf5; padding:9px 18px;
    border:1px solid rgba(255,255,255,.1); border-radius:8px; background:rgba(255,255,255,.02); }
  .fl-arrow{ color:#4d5568; font-size:14px; margin:5px 0 5px 20px; }
  {{-- Architecture は「順番(↓)」でなく「構造(│)」：細い縦のコネクタ線 --}}
  .fl-line{ width:1px; height:22px; background:rgba(255,255,255,.16); margin-left:24px; }

  .ref-list{ list-style:none; padding:0; margin:14px 0; }
  .ref-list li{ font-family:'JetBrains Mono',monospace; font-size:14px; line-height:1.5; color:#c2ccdb;
    padding:8px 0 8px 20px; position:relative; border-bottom:1px solid rgba(255,255,255,.05); }
  .ref-list li::before{ content:'·'; position:absolute; left:5px; color:#7d8aa0; }

  .ref-steps{ list-style:none; padding:0; margin:14px 0; counter-reset:s; }
  .ref-steps li{ font-family:'JetBrains Mono',monospace; font-size:14px; line-height:1.5; color:#c2ccdb;
    padding:10px 0 10px 36px; position:relative; border-bottom:1px solid rgba(255,255,255,.05); counter-increment:s; }
  .ref-steps li::before{ content:counter(s); position:absolute; left:2px; top:9px; font-size:11px; color:#7d8aa0;
    border:1px solid rgba(125,138,160,.4); border-radius:4px; width:19px; height:19px; display:flex; align-items:center; justify-content:center; }

  .ref-score{ margin:20px 0; }
  .sc-inputs{ display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px; }
  .sc-chip{ font-family:'JetBrains Mono',monospace; font-size:14px; color:#c2ccdb; padding:8px 14px;
    border:1px solid rgba(255,255,255,.1); border-radius:8px; background:rgba(255,255,255,.02); }
  .sc-arrow{ font-family:'JetBrains Mono',monospace; color:#4d5568; font-size:13px; margin:0 0 10px 6px; }
  .sc-out{ font-family:'JetBrains Mono',monospace; font-size:15px; color:#e8edf5; padding:9px 16px;
    border:1px solid rgba(125,138,160,.35); border-radius:8px; display:inline-block; }

  .ref-api{ margin:16px 0; }
  .ep{ display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid rgba(255,255,255,.06); }
  .ep .m{ font-family:'JetBrains Mono',monospace; font-size:11px; font-weight:700; letter-spacing:1px; padding:3px 9px; border-radius:5px; min-width:46px; text-align:center; }
  .ep .m.get{ color:#3fb950; background:rgba(63,185,80,.1); }
  .ep .m.post{ color:#d6a531; background:rgba(214,165,49,.1); }
  .ep .path{ font-family:'JetBrains Mono',monospace; font-size:14px; color:#e8edf5; }

  .ref-last{ margin-top:52px; padding-top:22px; border-top:1px solid rgba(255,255,255,.07);
    font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:.5px; color:#4d5568; line-height:1.9; }
</style>
