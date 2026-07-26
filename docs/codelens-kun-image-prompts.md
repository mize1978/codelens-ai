# CodeLensくん — Image Prompt Pack（v1 · 2026-07-26）

> 設定資料集 [`codelens-kun-character-sheet.md`](./codelens-kun-character-sheet.md) の作画用プロンプト。
> **8枚とも同じ人格。** 感情でなくポーズで語る司書。
> 出力：透過PNG・全身・中央・一貫した画風。**同じ seed / スタイルで通しで生成**すると揃う。

---

## ★ BASE RULE（全プロンプトの先頭に必ず入れる）

```
The same character in every illustration. Small white robot librarian with a glossy
circular face displaying the CodeLens symbol (cyan and purple), tiny gold crown,
deep blue starry cape, white body, coding badge (</>), soft Pixar-quality 3D
illustration, clean white outline, transparent background, consistent proportions
and facial design across every image.
```

**表情ルール:** 基本は 😐（neutral）。**笑顔 😊 は 07 Lantern のみ**（レビュー成功）。他はポーズで語る。

---

## 01 — Reading（Articles）　😐

```
The same character in every illustration. Small white robot librarian with a glossy
circular face displaying the CodeLens symbol (cyan and purple), tiny gold crown,
deep blue starry cape, white body, coding badge (</>), soft Pixar-quality 3D
illustration, clean white outline, transparent background, consistent proportions
and facial design across every image.
Full body, centered. He holds an open book with both hands, completely absorbed in
reading, eyes on the pages, not looking at the viewer. Calm, quiet, neutral
expression. Soft even lighting.
```

## 02 — Searching（Repository Intelligence）　😐

```
[BASE RULE]
Full body, centered. He holds up a magnifying glass and inspects lines of floating
code with curiosity, leaning in. A single small blue bookmark is tucked into a closed
book under his arm. Focused, neutral expression.
```

## 03 — Reviewing（Workspace）　😐

```
[BASE RULE]
Full body, centered. He sits at a small laptop whose screen shows lines of code,
leaning slightly forward, concentrated and serious. Neutral expression.
```

## 04 — Shelving（Reviews）　😐　★署名ポーズ

```
[BASE RULE]
Full body, centered. He is placing one book back onto a bookshelf: right hand gently
sliding the book into its place on the shelf, left hand holding a small blue bookmark.
Careful, quiet motion. Neutral expression.
```

## 05 — Blueprint（Docs）　😐

```
[BASE RULE]
Full body. He kneels over a large blueprint spread on the floor, holding a pen with a
ruler resting beside him, studying the plans. Quiet and studious. Neutral expression.
```

## 06 — Waiting（空の棚 / Reviews 0件）　😐

```
[BASE RULE]
Full body, centered. He sits on a simple chair in front of an empty bookshelf, a
closed book beside him, a small blue bookmark resting on his lap. Simply waiting,
patient and calm. Neutral expression.
```

## 07 — Lantern（レビュー完了後）　😊　※唯一の笑顔

```
[BASE RULE]
Full body, centered. He walks while carrying a warm glowing lantern in one hand, and a
book with a small blue bookmark in the other. Cozy "the work is done" atmosphere, warm
lantern light. A gentle happy smile — his only smiling pose.
```

## 08 — Idle（ロード / 空ページ / 404 / メンテ）　😐

```
[BASE RULE]
Full body, centered. He sits relaxed on a chair, book closed on his lap, idly twirling
a small blue bookmark between his fingers, quietly waiting with nothing to do. Neutral,
at ease.
```

---

## 使い方メモ

- `[BASE RULE]` は 01 に書いた全文をそのまま貼る（各プロンプトで先頭固定）。
- 🔖 **青い栞（Bookmark, cyan #38bdf8）** は 02 / 04 / 06 / 07 / 08 に登場＝Remember の象徴を通底させる。
- 透過PNG・全身・中央で書き出し、各部屋の余白に小さく配置する（UIを説明させない・2〜3秒で去る）。
- 迷ったら **04 Shelving が基準カット**（このキャラの本質＝読んで、棚に戻す）。

---

## Review Archive（空状態）v2 — 確定仕様

**維持する（実証済み・触らない）**
- **左の余白は埋めない** → 空白ではなく「静けさ」。図書館の空気を保つための余白。
  件数 / Tips / アイコン / 装飾カード を足した瞬間に静けさは崩れる。**何もないこと自体が意味。**
- **司書の存在感は現状維持** → 棚 ＜ 司書（約7%差）。「住人」がほんの少しだけ前に出る。
- **接地影は現状維持** → 影として認識されない濃さ（drop-shadow opacity ≈.16）。「そこに座っている」と感じるだけで十分。

**再レンダリング時のみ（CSSでは行わない）**
- **棚を司書側へ 3〜5°だけ向ける** → 「棚が司書を迎える／棚は司書の担当エリア」の構図。
  大きく向けると"演出"になる。3〜5°なら理由は意識されず、無意識に「棚と司書が同じ空間にいる」と感じる。ページ全体でなく**一枚絵で**調整。
- **01〜04：要・撮り直し** → 左上のプロンプト文字焼き込み＋暗背景。`transparent background, no text, no words` を強めてクリーンに。

**次の演出（未実装・席は空けてある）**
- 0件→1件の瞬間に **受け取り(16:25)** を 0.5〜0.8秒 → フェード → 空棚に最初の一冊。
  「保存された」でなく **「図書館に一冊増えた」** と感じさせる。→ Review Archive が機能でなく世界観になる。

> このv2は「気づかれないけれど印象だけ変わる」種類の最後の仕上げ。
> Review Archive を"一覧ページ"から"静かな図書館の一角"に変えたのは、派手な演出でなく
> **余白を守る・司書に喋らせない・棚を少し向ける** という小さな判断の積み重ね。
