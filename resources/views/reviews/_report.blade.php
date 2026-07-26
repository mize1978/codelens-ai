{{-- 🗂 Review Report 本編（show から抽出・巨大スコアヒーロー/PR/footerは除外）--}}
@php
    $data  = $review->review_data ?? [];
    $stats = $data['github_stats'] ?? [];
    $overall = $review->overall_score;
    $label   = $review->score_label;
    $color   = $review->score_color;
    $sublabel = match(true) {
        $overall >= 80 => '最高レベルのコード品質',
        $overall >= 60 => '改善余地があります',
        $overall >= 40 => 'いくつかの問題があります',
        default        => '大幅な改善が必要です',
    };

    $severityMeta = [
        'critical'   => ['emoji' => '🟥', 'label' => 'Critical',    'cls' => 'sev-critical'],
        'warning'    => ['emoji' => '🟧', 'label' => 'Warning',     'cls' => 'sev-warning'],
        'suggestion' => ['emoji' => '🟨', 'label' => 'Suggestion',  'cls' => 'sev-suggestion'],
    ];

    $issues = $data['issues'] ?? [];
    usort($issues, function($a, $b) {
        $order = ['critical' => 0, 'warning' => 1, 'suggestion' => 2];
        return ($order[$a['severity'] ?? 'suggestion'] ?? 2) <=> ($order[$b['severity'] ?? 'suggestion'] ?? 2);
    });

    $qScore = $review->quality_score ?? 0;
    $sScore = $review->security_score ?? 0;
    $mScore = $review->maintainability_score ?? 0;
@endphp

<style>
.show-page { max-width: 860px; margin: 0 auto; padding: 0 16px 60px; }

/* Processing */
.processing-card { background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 40px; max-width: 600px; margin: 60px auto; text-align: center; }
.processing-header .proc-icon { font-size: 3rem; margin-bottom: 12px; animation: pulse 1.5s infinite; }
.processing-header h2 { font-size: 1.6rem; color: var(--cyan); margin: 0 0 6px; }
.proc-repo { color: var(--text-muted); font-family: monospace; }
.console-box { background: #000; border: 1px solid #333; border-radius: 8px; padding: 16px; height: 160px; overflow-y: auto; text-align: left; margin: 24px 0 16px; }
.console-line { font-family: monospace; font-size: 0.8rem; color: #00ff88; margin-bottom: 4px; }
.progress-bar-wrap { background: #1a1a2e; border-radius: 99px; height: 8px; overflow: hidden; }
.progress-bar { height: 100%; background: linear-gradient(90deg, var(--cyan), var(--blue)); border-radius: 99px; transition: width 0.4s; }
.progress-label { color: var(--text-muted); font-size: 0.85rem; margin-top: 8px; }
.proc-mascot { margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 10px; }
.proc-mascot {
  margin-top: 16px;
  display: flex; align-items: center; justify-content: center; gap: 2px;
}
.pm-main-wrap {
  position: relative; display: inline-block;
  width: 130px; height: 130px; flex-shrink: 0;
  margin-bottom: -10px; /* ② 少し上へ */
}
.pm-main-img {
  width: 130px; height: 130px; object-fit: contain;
  animation: pmFloat 3.2s ease-in-out infinite;
  filter: drop-shadow(0 4px 10px rgba(0,80,200,0.14));
}
/* ③ 楕円の足元影 */
.pm-main-wrap::after {
  content: '';
  position: absolute; bottom: -4px; left: 50%;
  transform: translateX(-50%);
  width: 60px; height: 8px;
  background: radial-gradient(ellipse, rgba(0,0,0,0.28) 0%, transparent 70%);
  pointer-events: none;
}
.pm-glint {
  position: absolute; top: 5%; left: -50%; width: 40%; height: 50%;
  background: linear-gradient(110deg, transparent 20%, rgba(255,240,180,0.7) 50%, transparent 80%);
  pointer-events: none; opacity: 0;
}
.pm-glint.flash { animation: pmGlint 0.6s ease-out forwards; }
@keyframes pmGlint {
  0%   { left: -50%; opacity: 0; }
  15%  { opacity: 1; }
  100% { left: 120%; opacity: 0; }
}
/* 95% 到達フラッシュ */
.pm-main-img.found {
  animation: pmFloat 3.2s ease-in-out infinite, mascotFlash 0.22s ease-out;
}
@keyframes mascotFlash {
  0%   { filter: drop-shadow(0 4px 10px rgba(0,80,200,0.14)) brightness(1); }
  45%  { filter: drop-shadow(0 0 28px rgba(140,220,255,0.95)) brightness(1.4); }
  100% { filter: drop-shadow(0 4px 10px rgba(0,80,200,0.14)) brightness(1); }
}
.pm-text {
  font-size: 0.76rem; color: var(--text-dim); font-style: italic;
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);
  border-radius: 10px 10px 10px 2px;
  padding: 7px 11px; white-space: nowrap;
  margin-left: -6px;
}
@keyframes pmFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-3px)} }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

/* ① Overall Hero */
.overall-hero { border-radius: 20px; background: linear-gradient(135deg, rgba(0,0,0,0.6) 0%, rgba(10,10,30,0.8) 100%); border: 2px solid var(--score-color, #4488ff); box-shadow: 0 0 40px color-mix(in srgb, var(--score-color, #4488ff) 30%, transparent); padding: 36px 40px; margin: 32px 0 16px; }
.overall-inner { display: flex; align-items: center; gap: 32px; }
.overall-number { font-size: 5rem; font-weight: 900; font-family: monospace; color: var(--score-color, #4488ff); text-shadow: 0 0 30px var(--score-color, #4488ff); line-height: 1; min-width: 140px; }
.overall-label { font-size: 1.6rem; font-weight: 700; color: var(--score-color, #4488ff); letter-spacing: 3px; text-transform: uppercase; }
.overall-sublabel { font-size: 0.75rem; color: rgba(255,255,255,0.38); letter-spacing: 0.08em; margin-top: 3px; }
.overall-repo { font-family: monospace; color: var(--text-muted); font-size: 1rem; margin-top: 6px; }
.overall-verdict { color: #ccc; font-style: italic; margin-top: 8px; font-size: 0.95rem; }

/* ② GitHub Stats */
.github-stats-bar { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; background: rgba(255,255,255,0.04); border: 1px solid var(--border); border-radius: 12px; padding: 14px 20px; margin-bottom: 12px; }
.coverage-bar { display: flex; align-items: center; gap: 10px; padding: 9px 16px; margin-bottom: 20px; border-radius: 10px; background: color-mix(in srgb, var(--cov-color) 6%, transparent); border: 1px solid color-mix(in srgb, var(--cov-color) 28%, transparent); }
.cov-icon { font-size: 0.85rem; }
.cov-files { font-size: 0.78rem; color: rgba(255,255,255,0.55); font-family: monospace; }
.cov-pct { font-size: 0.80rem; font-weight: 700; color: var(--cov-color); margin-left: 4px; }
.cov-label { font-size: 0.72rem; color: color-mix(in srgb, var(--cov-color) 70%, rgba(255,255,255,0.4)); letter-spacing: 0.04em; }
.gs-item { display: flex; align-items: center; gap: 5px; font-size: 0.9rem; }
.gs-icon { font-size: 1rem; }
.gs-val { font-weight: 700; color: #fff; font-family: monospace; }
.gs-key { color: var(--text-muted); font-size: 0.8rem; }
.gs-sep { color: var(--border); font-size: 1.2rem; }
.gs-badge { background: var(--blue); color: #fff; border-radius: 99px; padding: 2px 10px; font-size: 0.78rem; font-weight: 600; }

/* Individual scores */
.scores-row { display: flex; gap: 16px; justify-content: center; margin-bottom: 28px; }
.score-ring-card { display: flex; flex-direction: column; align-items: center; gap: 8px; background: rgba(255,255,255,0.03); border: 1px solid var(--border); border-radius: 14px; padding: 20px 28px; }
.score-ring-label { font-size: 0.82rem; color: var(--text-muted); text-align: center; }

/* Sections */
.section-card { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; padding: 24px; margin-bottom: 20px; }
.section-title { font-size: 1rem; font-weight: 700; color: var(--cyan); margin-bottom: 16px; letter-spacing: 0.5px; }
.summary-text { color: #ccc; line-height: 1.7; margin: 0; }
.bullet-list { margin: 0; padding-left: 20px; color: #ccc; line-height: 1.8; }
.priorities-card { border-color: rgba(255,170,0,0.30); background: rgba(255,140,0,0.04); }
.priorities-list { margin: 0; padding-left: 0; list-style: none; display: flex; flex-direction: column; gap: 10px; counter-reset: priority; }
.priorities-list li { display: flex; align-items: flex-start; gap: 12px; padding: 10px 14px; background: rgba(255,140,0,0.06); border: 1px solid rgba(255,140,0,0.14); border-radius: 8px; counter-increment: priority; }
.priorities-list li::before { content: counter(priority); flex-shrink: 0; width: 22px; height: 22px; background: rgba(255,170,0,0.18); border: 1px solid rgba(255,170,0,0.40); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 700; color: rgba(255,200,80,0.90); line-height: 1; padding-top: 1px; margin-top: 1px; }
.prio-body { flex: 1; display: flex; flex-direction: column; gap: 3px; }
.prio-file { font-family: monospace; font-size: 0.75rem; color: rgba(255,200,80,0.80); background: rgba(255,170,0,0.10); border: 1px solid rgba(255,170,0,0.20); border-radius: 4px; padding: 1px 7px; display: inline-block; width: fit-content; }
.prio-action { font-size: 0.86rem; color: rgba(255,255,255,0.80); line-height: 1.55; }
.prio-gh-link { flex-shrink: 0; margin-top: 2px; padding: 4px 8px; font-size: 0.72rem; color: rgba(0,200,255,0.65); border: 1px solid rgba(0,200,255,0.20); border-radius: 5px; text-decoration: none; transition: color 0.15s, border-color 0.15s; white-space: nowrap; }
.prio-gh-link:hover { color: rgba(0,220,255,1); border-color: rgba(0,200,255,0.55); }
.security-list li { color: #ffaa88; }
.review-closing-message { margin: 20px 0 0; padding-top: 16px; border-top: 1px solid var(--border); color: #7a9bb5; font-size: 0.82rem; text-align: left; letter-spacing: 0.3px; }

/* ③ Issues */
.issues-list { display: flex; flex-direction: column; gap: 14px; }
.issue-card { border-radius: 10px; padding: 16px; border-left: 4px solid; }
.sev-critical   { background: rgba(255,50,80,0.08);  border-color: #ff3250; }
.sev-warning    { background: rgba(255,140,0,0.08);  border-color: #ff8c00; }
.sev-suggestion { background: rgba(255,220,0,0.07); border-color: #ffdc00; }
.issue-header { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; flex-wrap: wrap; }
.issue-sev-badge { font-size: 0.82rem; font-weight: 700; }
.sev-critical .issue-sev-badge   { color: #ff6680; }
.sev-warning .issue-sev-badge    { color: #ffaa44; }
.sev-suggestion .issue-sev-badge { color: #ffdd44; }
.issue-file { font-family: monospace; font-size: 0.78rem; color: var(--text-muted); background: rgba(255,255,255,0.06); padding: 2px 8px; border-radius: 4px; }
.issue-title { font-weight: 700; color: #fff; margin-bottom: 6px; font-size: 0.95rem; }
.issue-desc { color: #aaa; font-size: 0.88rem; line-height: 1.6; }
.issue-actions { margin-top: 12px; }

/* ④ Fix with AI */
.btn-fix-ai { background: linear-gradient(135deg, #7b2ff7, #4488ff); border: none; color: #fff; padding: 8px 18px; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 600; transition: opacity 0.2s, transform 0.1s; }
.btn-fix-ai:hover:not(:disabled) { opacity: 0.85; transform: translateY(-1px); }
.btn-fix-ai:disabled { opacity: 0.5; cursor: default; }
.fix-result { margin-top: 12px; }
.fix-loading { color: var(--cyan); font-size: 0.85rem; padding: 10px; }
.fix-output pre { background: #0a0a1a; border: 1px solid #333; border-radius: 8px; padding: 14px; overflow-x: auto; margin: 0; }
.fix-output code { font-family: monospace; font-size: 0.82rem; color: #e6e6ff; white-space: pre-wrap; word-break: break-word; }
.fix-error { color: #ff6680; font-size: 0.85rem; padding: 10px; }

/* ─── Diff: GitHub-style ─── */
.diff-unified {
  border-radius: 8px; overflow: hidden;
  border: 1px solid rgba(255,255,255,0.08);
  font-family: 'SFMono-Regular', Consolas, monospace; font-size: 0.74rem;
  background: #0d0d1a; line-height: 1.65;
}
.diff-unified > div { display: flex; align-items: stretch; min-height: 1.65em; }
.dl-num {
  min-width: 36px; padding: 0 8px;
  text-align: right; color: rgba(255,255,255,0.18);
  background: rgba(255,255,255,0.025);
  border-right: 1px solid rgba(255,255,255,0.05);
  user-select: none; flex-shrink: 0; font-size: 0.68rem;
  display: flex; align-items: center; justify-content: flex-end;
}
.dl-code { padding: 0 10px; flex: 1; white-space: pre-wrap; word-break: break-all; display: flex; align-items: center; }
.dl-add            { background: rgba(0,255,100,0.07); }
.dl-add .dl-num    { color: rgba(0,255,136,0.45); background: rgba(0,255,100,0.06); }
.dl-add .dl-code   { color: #7ef58a; }
.dl-del            { background: rgba(255,60,80,0.09); }
.dl-del .dl-num    { color: rgba(255,100,120,0.5); background: rgba(255,60,80,0.06); }
.dl-del .dl-code   { color: #ff8099; }
.dl-ctx .dl-code   { color: rgba(255,255,255,0.38); }
.dl-hunk           { background: rgba(0,100,255,0.07); }
.dl-hunk .dl-num   { color: transparent; }
.dl-hunk .dl-code  { color: rgba(0,200,255,0.55); font-style: italic; }
.dl-hdr .dl-code   { color: rgba(255,255,255,0.25); }

/* ⑤ Patch summary */
.patch-summary {
  padding: 8px 14px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.07); border-radius: 8px;
}
.ps-title { font-size: 0.6rem; font-weight: 800; letter-spacing: 0.18em; color: rgba(255,255,255,0.3); margin-bottom: 6px; }
.ps-stats { display: flex; gap: 12px; align-items: center; }
.ps-adds { color: #00ff88; font-size: 0.75rem; font-weight: 700; font-family: monospace; }
.ps-dels { color: #ff6680; font-size: 0.75rem; font-weight: 700; font-family: monospace; }
.ps-sep  { color: rgba(255,255,255,0.12); font-size: 0.72rem; }

/* ① Before ════▶ After */
.fix-diff {
  display: grid; grid-template-columns: 1fr auto 1fr;
  align-items: center; gap: 12px;
}
.diff-arrow {
  display: flex; flex-direction: column; align-items: center; gap: 2px;
  color: var(--cyan); font-family: monospace; font-weight: 700;
  text-shadow: 0 0 10px rgba(0,200,255,0.9), 0 0 22px rgba(0,200,255,0.45);
  user-select: none; flex-shrink: 0;
}
.da-sym { font-size: 1.05rem; letter-spacing: -2px; line-height: 1; }
.diff-panel { border-radius: 8px; overflow: hidden; }
.diff-before { border: 1px solid rgba(255,70,100,0.3); }
.diff-after  { border: 1px solid rgba(0,255,136,0.3); }
.diff-label { padding: 5px 12px; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.15em; }
.diff-before .diff-label { background: rgba(255,70,100,0.12); color: #ff6680; }
.diff-after  .diff-label { background: rgba(0,255,136,0.1);  color: #00ff88; }
.diff-panel pre { background: #070710; padding: 12px; margin: 0; overflow-x: auto; }
.diff-panel code { font-family: monospace; font-size: 0.78rem; color: #ccc; white-space: pre-wrap; word-break: break-word; }

/* ③ AI Explanation */
.diff-explanation {
  padding: 14px 16px; border-radius: 10px;
  background: rgba(0,150,255,0.05); border: 1px solid rgba(0,150,255,0.18);
}
.expl-title {
  font-size: 0.65rem; font-weight: 800; letter-spacing: 0.2em;
  color: var(--cyan);
  text-shadow: 0 0 10px rgba(0,200,255,0.7), 0 0 20px rgba(0,200,255,0.3);
  margin-bottom: 8px;
}
.expl-divider {
  height: 1px; background: linear-gradient(to right, rgba(0,200,255,0.3), transparent);
  margin-bottom: 10px;
}
.expl-body  { font-size: 0.84rem; color: #c8c8d8; line-height: 1.7; }

/* ⑥ Score delta */
.score-delta-card {
  padding: 14px 18px; border-radius: 10px; text-align: center;
  background: rgba(0,255,136,0.04); border: 1px solid rgba(0,255,136,0.16);
}
.sd-label  {
  font-size: 0.63rem; font-weight: 800; letter-spacing: 0.2em;
  color: rgba(0,255,136,0.7); margin-bottom: 12px;
}
.sd-flow   { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.sd-from   { font-size: 2rem; font-weight: 900; font-family: monospace; color: #666; line-height: 1; }
.sd-arrow-down { color: rgba(0,255,136,0.5); font-size: 1rem; line-height: 1; }
.sd-to-row { display: flex; align-items: baseline; gap: 8px; }
.sd-to     { font-size: 2rem; font-weight: 900; font-family: monospace; color: #00ff88; line-height: 1; text-shadow: 0 0 16px rgba(0,255,136,0.5); }
.sd-delta  { font-size: 0.8rem; font-weight: 800; color: #00ff88; background: rgba(0,255,136,0.12); padding: 3px 9px; border-radius: 4px; }

/* ④ Action buttons */
.fix-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.btn-copy-patch, .btn-download-diff {
  flex: 1; text-align: center;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.12); color: #ccc;
  padding: 9px 16px; border-radius: 7px; font-size: 0.8rem; font-weight: 600;
  cursor: pointer; font-family: inherit; transition: background 0.2s, color 0.2s, border-color 0.2s, box-shadow 0.2s;
}
.btn-copy-patch:hover    { background: rgba(0,200,255,0.12); color: var(--cyan); border-color: rgba(0,200,255,0.35); box-shadow: 0 0 12px rgba(0,200,255,0.12); }
.btn-download-diff:hover { background: rgba(0,255,136,0.1); color: #00ff88; border-color: rgba(0,255,136,0.35); box-shadow: 0 0 12px rgba(0,255,136,0.1); }

.fix-section-gap { display: flex; flex-direction: column; gap: 10px; }
.fix-stage { animation: stagePulse 1s ease-in-out infinite; display: inline-block; }
@keyframes stagePulse { 0%,100%{opacity:1} 50%{opacity:0.5} }

@media (max-width: 640px) { .fix-diff { grid-template-columns: 1fr; } .diff-arrow { transform: rotate(90deg); } }

/* ⑤ PR Comment */
.pr-desc { color: var(--text-muted); font-size: 0.88rem; margin-bottom: 14px; }
.pr-actions { display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
.btn-pr { background: #238636; border: none; color: #fff; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-size: 0.88rem; font-weight: 600; transition: background 0.2s; }
.btn-pr:hover { background: #2ea043; }
.copy-feedback { color: #00ff88; font-size: 0.85rem; font-weight: 600; }
.pr-textarea { width: 100%; height: 200px; background: #0a0a1a; border: 1px solid #333; border-radius: 8px; color: #ccc; font-family: monospace; font-size: 0.8rem; padding: 12px; resize: vertical; box-sizing: border-box; }

/* Failed */
.failed-card { text-align: center; padding: 60px 40px; background: var(--surface); border: 1px solid rgba(255,50,80,0.22); border-radius: 16px; max-width: 500px; margin: 60px auto; }
.failed-icon { font-size: 3rem; margin-bottom: 16px; }
.failed-repo { color: var(--text-muted); font-size: 0.88rem; margin-bottom: 14px; }
.failed-reason { color: rgba(255,200,200,0.80); font-size: 0.85rem; line-height: 1.6; margin-bottom: 20px; }
.failed-detail { margin-bottom: 20px; }
.failed-detail summary { font-size: 0.75rem; color: var(--text-muted); cursor: pointer; margin-bottom: 8px; }
.failed-detail code { display: block; font-size: 0.70rem; color: rgba(255,100,100,0.70); background: rgba(255,50,50,0.05); border: 1px solid rgba(255,50,50,0.12); border-radius: 6px; padding: 10px 12px; text-align: left; line-height: 1.5; word-break: break-all; }
.failed-actions { display: flex; justify-content: center; }
.btn-retry { background: rgba(255,255,255,0.06); border: 1px solid var(--border); color: var(--text-muted); padding: 10px 22px; border-radius: 8px; text-decoration: none; font-size: 0.88rem; transition: background 0.2s; }
.btn-retry:hover { background: rgba(255,255,255,0.1); color: #fff; }
.proc-hint { font-size: 0.72rem; color: rgba(255,255,255,0.22); margin-top: 20px; letter-spacing: 0.02em; }

/* Nav */
.back-row { display: flex; gap: 12px; justify-content: center; margin-top: 32px; flex-wrap: wrap; }
.btn-back { background: rgba(255,255,255,0.06); border: 1px solid var(--border); color: var(--text-muted); padding: 10px 22px; border-radius: 8px; text-decoration: none; font-size: 0.88rem; transition: background 0.2s; }
.btn-back:hover { background: rgba(255,255,255,0.1); color: #fff; }
.btn-ranking { background: rgba(255,170,0,0.1); border: 1px solid rgba(255,170,0,0.3); color: #ffaa00; padding: 10px 22px; border-radius: 8px; text-decoration: none; font-size: 0.88rem; transition: background 0.2s; }
.btn-ranking:hover { background: rgba(255,170,0,0.2); }
.btn-share { background: rgba(0,200,255,0.08); border: 1px solid rgba(0,200,255,0.25); color: var(--cyan); padding: 10px 22px; border-radius: 8px; font-family: inherit; font-size: 0.88rem; cursor: pointer; transition: background 0.2s; }
.btn-share:hover { background: rgba(0,200,255,0.15); }

/* Review footer */
/* Ending animation scroll reveal */
#scroll-mascot {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.9s ease, transform 0.9s ease;
  margin: 56px 0 32px;
}
#scroll-mascot.visible { opacity: 1; transform: translateY(0); }

.review-footer { margin-top: 56px; }
.rf-divider {
  height: 1px; margin-bottom: 36px;
  background: linear-gradient(90deg, transparent, var(--border), var(--border), transparent);
}
.rf-body {
  display: flex; align-items: center; justify-content: center;
  flex-wrap: wrap; gap: 32px; line-height: 1;
}
.rf-left { display: flex; align-items: center; gap: 14px; }
.rf-logo { width: 32px; height: 32px; border-radius: 8px; opacity: 0.65; }
.rf-generated { font-size: 0.8rem; color: var(--text-mute); margin-bottom: 4px; }
.rf-generated strong { color: var(--text-dim); font-weight: 600; }
.rf-credit { font-size: 0.7rem; color: var(--text-mute); }
.rf-credit strong { color: var(--text-dim); font-weight: 600; }
.rf-github-btn {
  display: inline-flex; align-items: center; gap: 11px;
  padding: 13px 20px; border-radius: 10px;
  border: 1px solid var(--border); color: var(--text-dim);
  text-decoration: none; transition: border-color 0.2s, color 0.2s, background 0.2s;
}
.rf-github-btn:hover {
  border-color: rgba(0,200,255,0.4); color: var(--cyan);
  background: rgba(0,200,255,0.05);
}
.rf-btn-logo {
  width: 30px; height: 30px; border-radius: 7px;
  opacity: 0.75; flex-shrink: 0; transition: opacity 0.2s;
}
.rf-github-btn:hover .rf-btn-logo { opacity: 1; }
.rf-btn-inner { display: flex; flex-direction: column; gap: 4px; }
.rf-btn-title { font-size: 0.78rem; font-weight: 600; letter-spacing: 0.04em; }
.rf-btn-sub { font-size: 0.62rem; color: rgba(140,180,220,0.35); letter-spacing: 0.02em; transition: color 0.2s; }
.rf-github-btn:hover .rf-btn-sub { color: rgba(0,200,255,0.5); }
.rf-version {
  font-size: 0.62rem; color: var(--text-mute);
  font-weight: 400; letter-spacing: 0.05em;
}

@media (max-width: 600px) {
    .overall-inner { flex-direction: column; text-align: center; gap: 16px; }
    .overall-number { font-size: 4rem; min-width: unset; }
    .scores-row { flex-direction: column; align-items: center; }
    .github-stats-bar { gap: 8px; }
}
</style>

{{-- Review Findings ＝ 本編の章扉（"ここから本編"の一行を添える）--}}
<div class="report-open">
  <div class="ro-title">Review Findings</div>
  <div class="ro-underline"></div>
  <div class="ro-sub">AI が優先度順に選んだ {{ count($data['analyzed_files'] ?? []) }} 件のファイルから生成</div>
</div>

{{-- ② GITHUB STATS --}}
@if(!empty($stats))
<div class="github-stats-bar">
    <div class="gs-item"><span class="gs-icon">⭐</span> <span class="gs-val">{{ number_format($stats['stars'] ?? 0) }}</span> <span class="gs-key">Stars</span></div>
    <div class="gs-sep">·</div>
    <div class="gs-item"><span class="gs-icon">🍴</span> <span class="gs-val">{{ number_format($stats['forks'] ?? 0) }}</span> <span class="gs-key">Forks</span></div>
    <div class="gs-sep">·</div>
    <div class="gs-item"><span class="gs-icon">📝</span> <span class="gs-val">{{ number_format($stats['commits'] ?? 0) }}</span> <span class="gs-key">Commits</span></div>
    <div class="gs-sep">·</div>
    <div class="gs-item"><span class="gs-icon">💬</span> <span class="gs-val">{{ number_format($stats['open_issues'] ?? 0) }}</span> <span class="gs-key">Issues</span></div>
    @if(!empty($stats['language']))
    <div class="gs-sep">·</div>
    <div class="gs-item"><span class="gs-icon">🔵</span> <span class="gs-val">{{ $stats['language'] }}</span></div>
    @endif
    @if(!empty($data['framework']) && $data['framework'] !== 'Unknown' && $data['framework'] !== 'なし')
    <div class="gs-sep">·</div>
    <div class="gs-item"><span class="gs-badge">{{ $data['framework'] }}</span></div>
    @endif
</div>
@endif

{{-- COVERAGE BADGE --}}
@php
    $analyzedCount = count($data['analyzed_files'] ?? []);
    $totalCount    = $data['total_file_count'] ?? 0;
@endphp
@if($analyzedCount > 0)
<div class="coverage-bar" style="--cov-color: #4488ff">
    <span class="cov-icon">🔍</span>
    <span class="cov-files">重要ファイル {{ $analyzedCount }} 件を精査</span>
    @if($totalCount > 0)
    <span class="cov-label">全 {{ number_format($totalCount) }} ファイル中 AI が優先度順に選択</span>
    @endif
</div>
@endif

{{-- INDIVIDUAL SCORES --}}
<div class="scores-row">
    @foreach([
        ['label'=>'品質','score'=>$qScore,'icon'=>'✦'],
        ['label'=>'セキュリティ','score'=>$sScore,'icon'=>'🛡'],
        ['label'=>'保守性','score'=>$mScore,'icon'=>'⚙']
    ] as $sc)
    @php
        $sc_color = $sc['score'] >= 80 ? '#00ff88' : ($sc['score'] >= 60 ? '#4488ff' : ($sc['score'] >= 40 ? '#ffaa00' : '#ff4466'));
        $offset = 283 * (1 - $sc['score'] / 100);
    @endphp
    <div class="score-ring-card">
        <svg viewBox="0 0 100 100" width="90" height="90">
            <circle cx="50" cy="50" r="45" fill="none" stroke="#1a1a2e" stroke-width="8"/>
            <circle class="ring-arc" cx="50" cy="50" r="45" fill="none" stroke="{{ $sc_color }}" stroke-width="8"
                stroke-dasharray="283" stroke-dashoffset="283"
                stroke-linecap="round" transform="rotate(-90 50 50)"
                data-final-offset="{{ $offset }}"
                style="transition: stroke-dashoffset 0.9s cubic-bezier(0.25,0.46,0.45,0.94); filter: drop-shadow(0 0 4px {{ $sc_color }})"/>
            <text class="ring-num" x="50" y="54" text-anchor="middle" fill="{{ $sc_color }}" font-size="20" font-weight="bold" font-family="monospace" data-target="{{ $sc['score'] }}">0</text>
        </svg>
        <div class="score-ring-label">{{ $sc['icon'] }} {{ $sc['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- ② TOP PRIORITIES（Fixesが主役なので概要より前に）--}}
@if(!empty($data['top_priorities']))
<div class="section-card priorities-card">
    <div class="section-title">🎯 今すぐ直すべき3点</div>
    <ol class="priorities-list">
        @foreach($data['top_priorities'] as $p)
        @php
            $pFile   = is_array($p) ? ($p['file']   ?? null) : null;
            $pAction = is_array($p) ? ($p['action'] ?? $p)   : $p;
            $ghUrl   = $pFile
                ? "https://github.com/{$review->owner}/{$review->repo}/blob/{$review->branch}/{$pFile}"
                : null;
        @endphp
        <li>
            <div class="prio-body">
                @if($pFile)
                <span class="prio-file">{{ $pFile }}</span>
                @endif
                <span class="prio-action">{{ $pAction }}</span>
            </div>
            @if($ghUrl)
            <a class="prio-gh-link" href="{{ $ghUrl }}" target="_blank" rel="noopener" title="GitHubで開く">↗</a>
            @endif
        </li>
        @endforeach
    </ol>
</div>
@endif

{{-- SUMMARY（脇役・概要）--}}
@if(!empty($data['summary']))
<div class="section-card">
    <div class="section-title">📋 概要</div>
    <p class="summary-text">{{ $data['summary'] }}</p>
</div>
@endif

{{-- ここから全文（要約↑ Top3・概要 / 全文↓ Issues・Good・Refactor・Security）--}}
<div class="findings-split"><span>Full Findings</span></div>

{{-- ③ ISSUES with severity --}}
@if(!empty($issues))
<div class="section-card">
    <div class="section-title">🔍 検出された問題 ({{ count($issues) }}件)</div>
    <div class="issues-list">
        @foreach($issues as $idx => $issue)
        @php
            $sev = $issue['severity'] ?? 'suggestion';
            $meta = $severityMeta[$sev] ?? $severityMeta['suggestion'];
        @endphp
        <div class="issue-card {{ $meta['cls'] }}" id="issue-{{ $idx }}">
            <div class="issue-header">
                <span class="issue-sev-badge">{{ $meta['emoji'] }} {{ $meta['label'] }}</span>
                @if(!empty($issue['file']))
                <span class="issue-file">📄 {{ $issue['file'] }}</span>
                @endif
            </div>
            <div class="issue-title">{{ $issue['title'] }}</div>
            <div class="issue-desc">{{ $issue['description'] }}</div>
            {{-- ④ Fix with AI button --}}
            <div class="issue-actions">
                <button class="btn-fix-ai"
                    onclick="fixWithAI({{ $idx }}, {{ json_encode($issue['title']) }}, {{ json_encode($issue['description']) }}, {{ json_encode($issue['file'] ?? '') }})">
                    ✨ Fix with AI
                </button>
            </div>
            <div class="fix-result" id="fix-result-{{ $idx }}" style="display:none"></div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- STRENGTHS --}}
@if(!empty($data['strengths']))
<div class="section-card">
    <div class="section-title">✅ 良い点</div>
    <ul class="bullet-list">
        @foreach($data['strengths'] as $s)
        <li>{{ $s }}</li>
        @endforeach
    </ul>
    <p class="review-closing-message">作り切ることが第一歩。改善を続けることがエンジニアリング。</p>
</div>
@endif

{{-- REFACTOR --}}
@if(!empty($data['refactor_suggestions']))
<div class="section-card">
    <div class="section-title">🔧 リファクタ提案</div>
    <ul class="bullet-list">
        @foreach($data['refactor_suggestions'] as $r)
        <li>{{ $r }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- SECURITY NOTES --}}
@if(!empty($data['security_notes']))
<div class="section-card">
    <div class="section-title">🔒 セキュリティ注意点</div>
    <ul class="bullet-list security-list">
        @foreach($data['security_notes'] as $n)
        <li>{{ $n }}</li>
        @endforeach
    </ul>
</div>
@endif
