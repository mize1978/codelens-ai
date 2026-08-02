{{-- ═══════════════════════════════════════════════════════════════════
     Editorial masthead — Articles / Reviews / Docs で共有する「一冊の雑誌」のリズム。
     StyleBook (reviews.stylebook) で確立した文法を編集部全体へ横展開する共通CSS。
       ← Workspace  →  eyebrow(EN)  →  title(EN・gradient)  →  lead(EN)
                    →  verse(JP 一呼吸 + EN accent)  →  rail  →  本文
     ページごとに変わるのは「内容」だけ。デザインは毎回変えない。
     ─ 見出し・コピーは英語 / 読む一節は日本語（言語バランスの規則） ─
     ═══════════════════════════════════════════════════════════════════ --}}
<style>
  /* 同じ建物の中：世界(#81)を各ページの背後にも薄く残す */
  .ed-sky{ position:fixed; inset:0; z-index:-1;
    background:
      radial-gradient(1100px 620px at 50% -8%, rgba(56,120,190,.13), transparent 60%),
      radial-gradient(900px 520px at 85% 12%, rgba(120,80,190,.09), transparent 62%); }

  /* 同じ余白 */
  .ed-wrap{ max-width:760px; margin:0 auto; padding:40px 20px 96px; }

  /* Workspace(/) へ戻る扉 — StyleBook と同じ位置・同じ声 */
  .ed-back{ display:inline-block; margin-bottom:22px; font-size:.66rem; letter-spacing:.14em;
    text-transform:uppercase; color:rgba(0,205,255,.6); text-decoration:none; }
  .ed-back:hover{ color:var(--cyan); }

  /* 同じ見出し */
  .ed-head{ text-align:center; margin:8px 0 0; }
  .ed-eyebrow{ font-size:.62rem; font-weight:700; letter-spacing:.38em; color:var(--cyan);
    text-transform:uppercase; opacity:.85; }
  .ed-title{ font-size:clamp(24px,4.4vw,34px); font-weight:700; letter-spacing:-.3px; line-height:1.22; margin:14px 0 0;
    background:linear-gradient(135deg,#ffffff,#8fc7ff); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
  .ed-lead{ font-size:.82rem; line-height:1.9; color:rgba(204,232,255,.55); margin:16px auto 0; max-width:560px; }

  /* 同じ呼吸：一呼吸置く一節 */
  .ed-verse{ max-width:470px; margin:38px auto 60px; text-align:center; }
  .ed-verse p{ font-size:.9rem; line-height:2.05; color:rgba(204,232,255,.5); margin:0 0 18px; }
  .ed-verse p:last-child{ margin-bottom:0; }
  .ed-verse .jp{ color:#d5ecff; }
  .ed-verse .accent{ color:#c3e5ff; font-style:italic; }

  /* 同じレール */
  .ed-rail{ display:flex; align-items:center; margin:36px 0 22px; }
  .ed-rail::before,.ed-rail::after{ content:''; flex:1; height:1px; }
  .ed-rail::before{ background:linear-gradient(90deg, transparent, rgba(0,200,255,.22)); }
  .ed-rail::after { background:linear-gradient(270deg, transparent, rgba(0,200,255,.22)); }
  .ed-rail span{ padding:0 16px; font-size:.58rem; letter-spacing:.35em; color:rgba(255,255,255,.34); text-transform:uppercase; }

  /* 静かな締め（本文の後） */
  .ed-foot{ margin-top:40px; text-align:center; font-size:.7rem; letter-spacing:.04em;
    color:rgba(204,232,255,.32); line-height:1.95; }

  /* ── 共有のリスト文法（各ページの索引はこの行の呼吸を継ぐ） ── */
  .ed-list{ display:flex; flex-direction:column; }
  .ed-row{ position:relative; display:block; text-decoration:none; padding:24px 34px 24px 6px;
    border-bottom:1px solid rgba(0,200,255,.08); transition:transform .22s ease, padding-left .22s ease; }
  .ed-row:last-child{ border-bottom:none; }
  .ed-row::after{ content:'→'; position:absolute; right:8px; top:27px; font-size:.85rem; color:var(--cyan);
    opacity:0; transform:translateX(-6px); transition:opacity .22s ease, transform .22s ease; }
  .ed-row:hover{ transform:translateX(3px); }
  .ed-row:hover::after{ opacity:1; transform:translateX(0); }
  .ed-tag{ display:block; font-size:.58rem; font-weight:700; letter-spacing:.2em; text-transform:uppercase;
    color:var(--cyan); margin-bottom:9px; }
  .ed-row-h{ display:block; font-size:1.06rem; font-weight:600; color:#eaf5ff; margin-bottom:7px; letter-spacing:.01em;
    transition:color .2s ease; }
  .ed-row:hover .ed-row-h{ color:#fff; }
  .ed-row-sub{ display:block; font-size:.82rem; line-height:1.65; color:rgba(204,232,255,.55); }

  .ed-soon{ display:inline-block; margin-top:24px; font-size:.72rem; letter-spacing:.06em;
    text-transform:uppercase; color:rgba(204,232,255,.32); cursor:default; }

  @media (prefers-reduced-motion: reduce){
    html{ scroll-behavior:auto; }
    .ed-row, .ed-row:hover{ transition:none; transform:none; }
  }
</style>
