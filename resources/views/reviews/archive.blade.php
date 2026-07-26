@extends('layouts.app_preview')
@section('title', 'Review Archive — CodeLensAI')

@section('content')

{{-- 🌌 同じ建物の中：世界(#81) を本棚の背後にも薄く残す --}}
<div class="jr-sky"></div>

<div class="arc-wrap">

  {{-- 部屋の扉：採点一覧ではなく Repository Memory の本棚 --}}
  <header class="arc-head">
    <div class="arc-kicker">Repository Memory</div>
    <h1 class="arc-title">Review Archive</h1>
    {{-- 館の象徴：CodeLensの蔵書。レビューが存在するときだけ現れる（空状態は司書が主役）。一冊だけ。 --}}
    @if(($opening ?? false) || $reviews->count())
    <img src="/images/codelens-book.png" class="arc-book" alt="CodeLens — the repository's book">
    @endif
    <p class="arc-lead">Every review becomes part of the repository's memory.</p>
    <p class="arc-lead-ja">レビューは一度きりではない。<br>リポジトリの履歴として積み重なっていく。</p>
  </header>

  <div class="arc-rule"></div>

  @php $opening = $opening ?? false; $demo = $demo ?? false; @endphp

  @if($opening)
    {{-- 🎬 開館の日：総数=1（本当の最初の一冊）か デモ。Waiting→Receiving→Shelving→最初の一冊。
         Repository Intelligence の三つ目の動詞 "Remember" を、一度だけ芝居にする。
         二度と繰り返さない（localStorage で once-only）。2冊目以降はこの分岐に来ない。 --}}
    <div class="arc-empty" data-opening="1" @if($demo) data-demo="1" @endif>
      <div class="arc-lib" id="arcLib">
        <span class="arc-empty-fig" id="libWaiting"><img src="/images/codelens-waiting.png" class="arc-empty-img" alt="CodeLens is waiting at the empty shelf"></span>
        <span class="arc-empty-fig arc-lib-over is-off" id="libReceiving"><img src="/images/codelens-receiving.png" class="arc-empty-img arc-recv" alt="CodeLens receives the first book"></span>
      </div>
      <div id="recText">
        <p class="arc-empty-en" id="recEn">The shelf is empty.</p>
        <p class="arc-empty-sub" id="recSub">CodeLens is waiting for the first review.</p>
        <p class="arc-empty-ja">最初のレビューが、この棚の最初の一冊になる。</p>
      </div>
      @if($incoming)
        @php
          $isc = (int) $incoming->overall_score;
          $icol = $isc >= 60 ? '#3fb950' : ($isc >= 40 ? '#d6a531' : '#f2585a');
          $ird = $incoming->review_data ?? [];
          $iverdict = $ird['one_line_verdict'] ?? ($ird['summary'] ?? null);
        @endphp
        <a href="{{ route('reviews.show', $incoming) }}" class="arc-item arc-firstbook is-off" id="firstBook">
          <div class="arc-score" style="color:{{ $icol }}">{{ $isc }}<i>/100</i></div>
          <div class="arc-main">
            <div class="arc-repo">{{ $incoming->owner }}<span>/</span>{{ $incoming->repo }}</div>
            @if($iverdict)<div class="arc-verdict">{{ \Illuminate\Support\Str::limit($iverdict, 78) }}</div>@endif
          </div>
          <div class="arc-meta"><span class="arc-date">{{ $incoming->created_at?->format('Y.m.d') }}</span><span class="arc-open">↗</span></div>
        </a>
      @endif
    </div>
    <script>
    (function(){
      var root=document.querySelector('.arc-empty[data-opening]'); if(!root) return;
      var demo=root.hasAttribute('data-demo'),
          lib=document.getElementById('arcLib'),
          wait=document.getElementById('libWaiting'), recv=document.getElementById('libReceiving'),
          txt=document.getElementById('recText'), en=document.getElementById('recEn'),
          sub=document.getElementById('recSub'), book=document.getElementById('firstBook');
      function off(el,v){ if(el) el.classList.toggle('is-off', v); }
      function shelved(){ if(en) en.textContent='The first review is on the shelf.'; if(sub) sub.textContent='図書館に、最初の一冊。'; }
      // サーバーが total===1 のときだけこのHTMLが出る（＝"最初の一冊"の事実）。
      // ここは「このブラウザで受け取ったか」の担当。AND で本当に一度きり。
      var KEY='first_book_received';
      // 受け取り済み（本番）：セレモニーは流さず、最終状態（棚に一冊）だけ見せる
      if(!demo && localStorage.getItem(KEY)){
        off(recv,true); if(lib) lib.classList.add('collapsed'); shelved(); if(book) book.classList.remove('is-off'); return;
      }
      // Repository Memory 誕生の瞬間を記録する（見たフラグでなく "受け取った日時"）
      if(!demo){ try{ localStorage.setItem(KEY, new Date().toISOString()); }catch(e){} }
      // 三幕（一度きり）: 待つ → 立って受け取る
      setTimeout(function(){ off(wait,true); off(recv,false);
        if(en) en.textContent='A new review arrives.'; if(sub) sub.textContent='CodeLens receives the first book.'; }, 700);
      // 受け取ったら司書は退場（棚=一覧へ譲る）・文はフェード
      setTimeout(function(){ off(recv,true); if(lib) lib.classList.add('collapsed'); if(txt) txt.classList.add('is-off'); }, 1650);
      // 棚に最初の一冊が着地
      setTimeout(function(){ if(book) book.classList.remove('is-off'); shelved(); if(txt) txt.classList.remove('is-off'); }, 2270);
    })();
    </script>

  @elseif($reviews->count())
    {{-- 棚を開いた"呼吸"：件数の右にそっと並び順を添える --}}
    <div class="arc-shelf">
      <span class="arc-count">{{ $reviews->count() }} reviews remembered</span>
      <span class="arc-order">Newest first</span>
    </div>

    <div class="arc-list">
      @foreach($reviews as $r)
        @php
          $sc  = (int) $r->overall_score;
          $col = $sc >= 60 ? '#3fb950' : ($sc >= 40 ? '#d6a531' : '#f2585a');
          $rd  = $r->review_data ?? [];
          $verdict = $rd['one_line_verdict'] ?? ($rd['summary'] ?? null);
        @endphp
        <a href="{{ route('reviews.show', $r) }}" class="arc-item">
          <div class="arc-score" style="color:{{ $col }}">
            {{ $sc }}<i>/100</i>
          </div>
          <div class="arc-main">
            <div class="arc-repo">{{ $r->owner }}<span>/</span>{{ $r->repo }}</div>
            @if($verdict)
              <div class="arc-verdict">{{ \Illuminate\Support\Str::limit($verdict, 78) }}</div>
            @endif
          </div>
          <div class="arc-meta">
            <span class="arc-date">{{ $r->created_at?->format('Y.m.d') }}</span>
            <span class="arc-open">↗</span>
          </div>
        </a>
      @endforeach
    </div>

  @else
    {{-- 司書：常設の Waiting（0件・空の棚の前で静かに待つ）。演出なし。 --}}
    <div class="arc-empty">
      <div class="arc-lib">
        <span class="arc-empty-fig"><img src="/images/codelens-waiting.png" class="arc-empty-img" alt="CodeLens is waiting at the empty shelf"></span>
      </div>
      <p class="arc-empty-en">The shelf is empty.</p>
      <p class="arc-empty-sub">CodeLens is waiting for the first review.</p>
      <p class="arc-empty-ja">最初のレビューが、この棚の最初の一冊になる。</p>
    </div>
  @endif

</div>

<style>
  .jr-sky{ position:fixed; inset:0; z-index:-1;
    background:
      radial-gradient(1100px 620px at 50% -8%, rgba(56,120,190,.14), transparent 60%),
      radial-gradient(900px 520px at 85% 12%, rgba(120,80,190,.10), transparent 62%); }

  .arc-wrap{ max-width:760px; margin:0 auto; padding:64px 24px 96px;
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Inter,system-ui,sans-serif; }

  .arc-head{ text-align:center; margin-bottom:30px; }
  /* 館の象徴：一冊だけ。暗背景を放射マスクでページに溶かす */
  .arc-book{ display:block; width:min(158px,44%); height:auto; margin:16px auto 20px; }
  .arc-kicker{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:3px; text-transform:uppercase; color:#6b7488; margin-bottom:14px; }
  .arc-title{ font-size:clamp(30px,5vw,40px); font-weight:700; color:#e8edf5; margin:0 0 16px; letter-spacing:-.5px; }
  .arc-lead{ font-size:15px; color:#8792a6; margin:0 0 10px; letter-spacing:.2px; }
  .arc-lead-ja{ font-size:14px; line-height:1.8; color:#9aa4b8; margin:0; }

  .arc-rule{ height:1px; background:rgba(255,255,255,.09); margin:0 0 18px; }
  .arc-shelf{ display:flex; justify-content:space-between; align-items:baseline; margin-bottom:12px; }
  .arc-count{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:1.5px; text-transform:uppercase; color:#4d5568; }
  .arc-order{ font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:1.5px; text-transform:uppercase; color:#4d5568; }

  .arc-list{ display:flex; flex-direction:column; }
  .arc-item{ display:flex; align-items:center; gap:20px; padding:20px 6px; text-decoration:none;
    border-bottom:1px solid rgba(255,255,255,.06); transition:transform .2s ease, padding-left .2s ease; }
  .arc-item:hover{ transform:translateX(2px); padding-left:12px; }

  .arc-score{ flex:0 0 62px; font-family:'JetBrains Mono',monospace; font-size:26px; font-weight:700; letter-spacing:-1px; }
  .arc-score i{ font-style:normal; font-size:11px; color:#4d5568; font-weight:400; margin-left:1px; }

  .arc-main{ flex:1; min-width:0; }
  .arc-repo{ font-size:16px; font-weight:600; color:#e8edf5; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .arc-repo span{ color:#4d5568; margin:0 2px; font-weight:400; }
  .arc-item:hover .arc-repo{ color:#fff; }
  .arc-verdict{ font-size:13px; line-height:1.55; color:#8792a6; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

  .arc-meta{ flex:0 0 auto; display:flex; align-items:center; gap:14px; }
  .arc-date{ font-family:'JetBrains Mono',monospace; font-size:12px; color:#5b6474; }
  .arc-open{ font-family:'JetBrains Mono',monospace; font-size:14px; color:#38bdf8; opacity:0; transform:translateX(-6px); transition:opacity .2s ease, transform .2s ease; }
  .arc-item:hover .arc-open{ opacity:1; transform:translateX(0); }

  .arc-empty{ text-align:center; padding:56px 0; }
  {{-- 司書：待つ→受け取る→席に戻る。棚<司書・接地影・マスクで暗背景を溶かす --}}
  .arc-lib{ position:relative; width:min(342px,78%); margin:0 auto 24px; max-height:700px;
    transition:max-height .6s ease, opacity .5s ease, margin .6s ease; }
  {{-- 受け取り後：司書は退場して「棚＝一覧」へ譲る（0→1 の実挙動と一致）--}}
  .arc-lib.collapsed{ max-height:0; opacity:0; margin:0; overflow:hidden; }
  .arc-empty-fig{ display:block; filter:drop-shadow(0 15px 22px rgba(0,0,0,.16)); transition:opacity .45s ease; }
  .arc-empty-img{ display:block; width:100%; height:auto;
    -webkit-mask-image:radial-gradient(ellipse 76% 74% at 57% 47%, #000 54%, transparent 100%);
            mask-image:radial-gradient(ellipse 76% 74% at 57% 47%, #000 54%, transparent 100%); }
  {{-- Receiving（透過・立って受け取る）を待機の上に重ねて底辺を合わせる --}}
  .arc-lib-over{ position:absolute; left:0; right:0; bottom:0; }
  .arc-recv{ -webkit-mask-image:none !important; mask-image:none !important; width:86%; margin:0 auto; }
  #recText{ transition:opacity .45s ease; }
  .arc-firstbook{ margin-top:28px; text-align:left;
    border-top:1px solid rgba(255,255,255,.06); border-bottom:1px solid rgba(255,255,255,.06);
    transition:opacity .55s ease, transform .55s ease; }
  .arc-firstbook.is-off{ transform:translateY(14px); }
  .is-off{ opacity:0 !important; }
  .arc-empty-en{ font-size:16px; color:#9aa4b8; margin:0 0 10px; }
  .arc-empty-sub{ font-family:'JetBrains Mono',monospace; font-size:12.5px; letter-spacing:.5px; color:#7d8aa0; margin:0 0 16px; }
  .arc-empty-ja{ font-size:13px; color:#5b6474; margin:0; }

  @media (max-width:560px){
    .arc-verdict{ display:none; }
    .arc-score{ flex-basis:52px; font-size:22px; }
  }
</style>

@endsection
