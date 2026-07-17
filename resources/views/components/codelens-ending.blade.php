{{--
  CodeLensくん Ending Animation
  ──────────────────────────────
  Architecture note (Lottie migration path):
    - All frame state is managed by window.CLEnding.showFrame(n)
    - Physical animations (breathe, glow) are CSS-only keyframes
    - To migrate to Lottie: replace .cl-frame[data-frame] with <lottie-player>
      and call lottieplayer.goToAndPlay(frame) instead of CLEnding.showFrame(n)
--}}

<div class="cl-ending" id="cl-ending" aria-hidden="true">

  {{-- Hidden preload to avoid first-frame flicker --}}
  <div class="cl-preload">
    @foreach(['01_front','02_look_right','03_turn_back','04_wave_1','05_wave_2','06_blink'] as $fname)
    <img src="/images/ending/{{ $fname }}.png" alt="" fetchpriority="high">
    @endforeach
  </div>

  {{-- Stage: all frames stacked, JS controls opacity --}}
  <div class="cl-stage" id="cl-stage">
    @php $frameNames = ['01_front','02_look_right','03_turn_back','04_wave_1','05_wave_2','06_blink']; @endphp
    @foreach($frameNames as $i => $fname)
    <div class="cl-frame" data-frame="{{ $i + 1 }}" id="clf-0{{ $i + 1 }}">
      <img src="/images/ending/{{ $fname }}.png" alt="CodeLensくん" draggable="false">
    </div>
    @endforeach

    {{-- Lantern warmth glow (position tuned to lower-left lantern area) --}}
    <div class="cl-lantern-glow" aria-hidden="true"></div>
  </div>

</div>

<style>
/* ── Layout ─────────────────────────────── */
.cl-ending {
  width: 100%;
  max-width: 300px;
  margin: 0 auto;
  user-select: none;
  pointer-events: none;
  -webkit-tap-highlight-color: transparent;
}

/* Preload hidden */
.cl-preload {
  display: none;
  position: absolute;
  width: 1px; height: 1px;
  overflow: hidden;
  visibility: hidden;
}

/* Stage: square aspect ratio */
.cl-stage {
  position: relative;
  width: 100%;
  padding-bottom: 100%;
  will-change: transform;
  animation: cl-breathe 4s ease-in-out infinite;
}

/* ── Physical animations (CSS-only) ─────── */

/* Breathing — 2px up/down, 0.3° gentle tilt */
@keyframes cl-breathe {
  0%, 100% { transform: translateY(0px)   rotate(0deg);   }
  30%       { transform: translateY(-1px)  rotate(0.15deg); }
  50%       { transform: translateY(-2px)  rotate(0.3deg);  }
  70%       { transform: translateY(-1px)  rotate(0.15deg); }
}

/* Lantern sway — rotates the glow overlay slightly */
@keyframes cl-lantern-sway {
  0%, 100% { transform: translateX(0)    rotate(0deg);   }
  25%       { transform: translateX(2px)  rotate(1.5deg);  }
  75%       { transform: translateX(-2px) rotate(-1.5deg); }
}

/* Lantern warmth pulse */
@keyframes cl-glow-pulse {
  0%, 100% { opacity: 0.55; transform: scale(1);    }
  40%       { opacity: 0.90; transform: scale(1.10); }
  60%       { opacity: 1.00; transform: scale(1.14); }
}

/* ── Frame layers ────────────────────────── */
.cl-frame {
  position: absolute;
  inset: 0;
  opacity: 0;
  will-change: opacity;
}

/* Fast transition for normal frames, ultra-fast for blink */
.cl-frame                { transition: opacity 0.15s ease-in-out; }
.cl-frame[data-frame="6"]{ transition: opacity 0.06s ease-in-out; }

/* Frame 1 starts visible */
#clf-01 { opacity: 1; }

.cl-frame img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  -webkit-user-drag: none;
}

/* ── Lantern glow overlay ────────────────── */
/* Position calibrated to lower-left lantern in the source images */
.cl-lantern-glow {
  position: absolute;
  bottom: 6%;
  left: 8%;
  width: 40%;
  padding-bottom: 40%;
  border-radius: 50%;
  background: radial-gradient(
    circle,
    rgba(255, 170,  60, 0.40) 0%,
    rgba(255, 110,  20, 0.18) 40%,
    rgba(255,  80,   0, 0.05) 65%,
    transparent 80%
  );
  pointer-events: none;
  animation:
    cl-glow-pulse   2.8s ease-in-out infinite,
    cl-lantern-sway 5.6s ease-in-out infinite;
  will-change: opacity, transform;
}
</style>

<script>
(function () {
  // ── Frame sequence (Lottie migration: replace showFrame calls with lottie goTo) ──
  // [frameIndex 0-based, durationMs]
  // Total = 8000ms for seamless loop
  const SEQ = [
    [0, 1000],  // 01_front      0.0–1.0s
    [1, 1000],  // 02_look_right 1.0–2.0s
    [2, 1200],  // 03_turn_back  2.0–3.2s
    [3,  800],  // 04_wave_1     3.2–4.0s
    [4, 1000],  // 05_wave_2     4.0–5.0s
    [5,  200],  // 06_blink      5.0–5.2s  (scheduled blink)
    [3,  600],  // 04_wave_1     5.2–5.8s
    [4,  600],  // 05_wave_2     5.8–6.4s
    [3,  600],  // 04_wave_1     6.4–7.0s
    [4,  800],  // 05_wave_2     7.0–7.8s
    [0,  200],  // 01_front      7.8–8.0s  (loop join)
  ];

  const FRAME_IDS = ['clf-01','clf-02','clf-03','clf-04','clf-05','clf-06'];
  let frames = [];
  let currentIdx = 0;
  let blinkLocked = false;
  let step = 0;
  let seqTimer = null;

  function showFrame(idx) {
    if (idx === currentIdx) return;
    const prev = frames[currentIdx];
    const next = frames[idx];
    if (prev) prev.style.opacity = '0';
    if (next) next.style.opacity = '1';
    currentIdx = idx;
  }

  // Expose for external control (e.g. pause on visibility hidden)
  window.CLEnding = { showFrame };

  function runStep() {
    const [fIdx, dur] = SEQ[step];
    showFrame(fIdx);
    step = (step + 1) % SEQ.length;
    seqTimer = setTimeout(runStep, dur);
  }

  // Random blink during wave phase (frames 3 or 4 = wave_1 or wave_2)
  function scheduleRandomBlink() {
    const delay = 5000 + Math.random() * 3000; // 5–8s random
    setTimeout(function () {
      if (!blinkLocked && (currentIdx === 3 || currentIdx === 4)) {
        blinkLocked = true;
        const savedIdx = currentIdx;
        showFrame(5); // 06_blink
        setTimeout(function () {
          showFrame(savedIdx);
          blinkLocked = false;
        }, 140);
      }
      scheduleRandomBlink();
    }, delay);
  }

  // Pause animation when tab is hidden (battery saving)
  document.addEventListener('visibilitychange', function () {
    if (document.hidden) {
      clearTimeout(seqTimer);
    } else {
      runStep();
    }
  });

  function init() {
    frames = FRAME_IDS.map(function (id) { return document.getElementById(id); });
    // Ensure frame 1 starts visible
    frames.forEach(function (f, i) { if (f) f.style.opacity = i === 0 ? '1' : '0'; });
    currentIdx = 0;
    runStep();
    scheduleRandomBlink();
  }

  // Wait for all frame images to load to prevent flicker
  const container = document.getElementById('cl-ending');
  if (!container) return;

  const imgs = container.querySelectorAll('.cl-frame img');
  let loaded = 0;
  const total = imgs.length;

  function onImageReady() {
    loaded++;
    if (loaded >= total) init();
  }

  if (total === 0) {
    init();
  } else {
    imgs.forEach(function (img) {
      if (img.complete && img.naturalWidth > 0) {
        onImageReady();
      } else {
        img.addEventListener('load',  onImageReady);
        img.addEventListener('error', onImageReady);
      }
    });
  }
})();
</script>
