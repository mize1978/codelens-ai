{{--
  CodeLensくん Ending — Review詳細ページ「完成形」
  ────────────────────────────────────────────
  静止画（ランタン司書＝見守りの姿勢）＋ ランタンの光だけが微かに明滅。
  「出来事の後の余韻」。司書は動かない＝建築の一部（暖炉・読書灯のように）。
    ❌ 呼吸 / まばたき / 手を振る / 視線移動 / ケープ揺れ
    ✅ ランタンの光だけ、ゆっくり明滅（環境だけ動く）
  旧アニメ版（8秒ループ・手振り・まばたき）は codelens-ending.blade.php.bak に退避。
--}}
<div class="cl-ending" aria-hidden="true">
  <div class="cl-stage">
    <img src="/images/codelens-lantern.png" class="cl-static" alt="CodeLensくん" draggable="false">
    {{-- ランタンの炎の位置に、光だけがゆっくり明滅 --}}
    <span class="cl-lantern-glow" aria-hidden="true"></span>
  </div>
</div>

<style>
.cl-ending{
  width: 100%;
  max-width: 360px;
  margin: 0 auto;
  user-select: none;
  pointer-events: none;
  -webkit-tap-highlight-color: transparent;
}
.cl-stage{ position: relative; }

/* 静止画：暗背景を放射マスクでページへ溶かす（四角い箱にしない）*/
.cl-static{
  display: block;
  width: 100%;
  height: auto;
  -webkit-user-drag: none;
  -webkit-mask-image: radial-gradient(ellipse 82% 84% at 50% 50%, #000 58%, transparent 100%);
          mask-image: radial-gradient(ellipse 82% 84% at 50% 50%, #000 58%, transparent 100%);
}

/* 環境だけ動く：ランタンの光がゆっくり明滅（3.6s）。司書自身は止まっている */
.cl-lantern-glow{
  position: absolute;
  left: 9%;
  bottom: 22%;
  width: 24%;
  padding-bottom: 24%;
  border-radius: 50%;
  background: radial-gradient(
    circle,
    rgba(255, 185,  80, 0.45) 0%,
    rgba(255, 130,  25, 0.16) 45%,
    transparent 75%
  );
  mix-blend-mode: screen;
  animation: cl-lantern-flicker 4.5s ease-in-out infinite;
  will-change: opacity, transform;
}
@keyframes cl-lantern-flicker{
  0%, 100% { opacity: 0.55; transform: scale(1);    }
  50%      { opacity: 1.00; transform: scale(1.07); }
}

@media (prefers-reduced-motion: reduce){
  .cl-lantern-glow{ animation: none; opacity: 0.8; }
}
</style>
