# CodeLensくん — Repository Librarian
### Character Sheet / 設定資料集　（v1 · 2026-07-26）

> 画像生成にかける前の「設定画」。ここを固定してから作画する。
> CodeLensくんは **マスコット** ではなく **世界の住人**。UIの一部ではなく、建物の中で働いている。

---

## 1. Base

**Role — Repository Librarian（リポジトリの記憶を管理する司書）**
レビューを書く AI ではない。**読む・整理する・戻す。**

**Visual Design（作画キャノン・全プロンプト共通）**
Small white robot librarian with a glossy circular face displaying the CodeLens symbol
(cyan and purple), tiny gold crown, deep blue starry cape, white body, coding badge
(`</>`), soft Pixar-quality 3D, clean white outline, transparent background.
→ 作画用プロンプトは [`codelens-kun-image-prompts.md`](./codelens-kun-image-prompts.md)（8カット）。

**Personality**
- 静か
- 急がない
- 説明しない
- 先回りしない
- ユーザーより前に立たない

**Never（やらないこと）**
- ✕ UI を説明する
- ✕ ヒントを連呼する
- ✕ マスコット化する
- ✕ ギャグ担当になる
- ✕ 常に画面にいる

**Always（やること）**
- ○ 読んでいる
- ○ 整理している
- ○ 本を戻している
- ○ 設計図を見ている
- ○ 灯りを持つ

---

## 2. Tools / 仕事道具

| 道具 | 用途 | 対応ポーズ |
|---|---|---|
| 📖 本 | 読む | 01 Reading |
| 🔍 虫眼鏡 | 探す | 02 Searching |
| 💻 ノートPC | レビューする | 03 Reviewing |
| 🏮 ランタン | 灯りを持つ | 07 Lantern |
| 🔖 **しおり / Bookmark** | **記憶を挟む（＝Remember）** | 全ポーズに携行可 |

### 🔖 The Bookmark（象徴）
司書は本を閉じるとき、栞を挟む。
CodeLensくんも **レビューを保存した瞬間、小さな青い栞を一枚、本に挟む。**
それが **Remember**。

> 「レビューを保存する」という動作が、
> 「記録を残す」ではなく **「栞を挟む」** という世界観になる。

CodeLensAI 最大のシンボル。色は建物のアクセント（cyan / #38bdf8）。

---

## 3. Pose List（作画リスト・7カット）

| No | Pose | Prop | 置き場所 | Trigger / Voice |
|----|------|------|---------|-----------------|
| 01 | **Reading** | 本 | Articles | （無言・本棚の上で読書）|
| 02 | **Searching** | 虫眼鏡 | Repository Intelligence | `Finding important files...` |
| 03 | **Reviewing** | ノートPC | Workspace | `Reading repository...` |
| 04 | **Shelving** | 本を棚へ戻す | Reviews | （無言・レビューを棚に並べる）|
| 05 | **Blueprint** | 設計図を広げる | Docs | （無言）|
| 06 | **Waiting** | 空の本棚 | Reviews（0件） | `The shelf is empty.` / `CodeLens is waiting for the first review.` |
| 07 | **Lantern** | ランタン | レビュー完了後 | `Memory updated.`（＋🔖 栞を挟む・唯一の😊）|
| 08 | **Idle** | 栞をくるくる回す | ロード / 空ページ / 404 / メンテ | （無言・待機。何もしてないけど何かしてる）|

---

## 4. Expression（表情）

```
基本   😐
  │
  ▼   ← レビュー成功のときだけ
笑顔   😊
```

普段は無表情（司書は感情を前に出さない）。**笑うのはレビュー成功の一瞬だけ。**

---

## 5. Animation（動き）

- 全部 **2〜3秒**
- **永続しない**
- 終わったら **消える**
- **UI だけ残る**

司書は作業が終われば去る。画面に居座らない。

---

## 6. Voice（声）

**基本、無言。** 喋るのは「仕事の状態」だけ。

```
Reading repository...
Finding important files...
Memory updated.
The shelf is empty.
CodeLens is waiting...
```

UI の説明・励まし・ヒントは一切言わない。状態の実況のみ。

---

## 実装ステータス（2026-07-26）

- ✅ 06 Waiting の **声** のみ実装済み（`resources/views/reviews/archive.blade.php` の空状態）
- ⬜ 01–05・07 の作画（本設定に沿って生成）→ 各部屋へ配置
- ⬜ 🔖 Bookmark を Remember（レビュー保存）の演出に接続

**鉄則:** 絵ができても *UIを説明させない / UIパーツにせず世界に住まわせる / 静かに、2〜3秒で去る*。
