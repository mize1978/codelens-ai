# Repository Intelligence — distinguish *unknown* from *absent*

> The model wasn't wrong because it couldn't read enough code.
> It was wrong because the system never told it what it hadn't read.

CodeLensAI が AI レビューに渡す前段で、**「確認できなかった情報」を「存在しない情報」として扱ってしまう**構造的欠陥を解消するための仕組み。

## 背景：なぜ必要か（RewardMe ドッグフーディング）

CodeLensAI を自作の Rails アプリ **RewardMe** に対して実行したところ、**同一構造の誤検出（False Positive）が2件**発生した。

- ❌ 「テストコードが存在しない」 → 実際は `spec/` に RSpec が4ファイル＋CIで green。
- ❌ 「User モデルに email validation が存在しない（Critical）」 → 実際は `user.rb:257` に `validates :email` が存在し、model spec もある。

原因は Claude の性能ではなく、**渡す前段でコンテキストの意味付けが失われていたこと**：

```
Repository 362 files
 → selectKeyFiles で最大12件に選抜（spec/ は構造的に選ばれにくい）
 → 選ばれたファイルも本文3000字で切断（user.rb は6920字→257行目のvalidationは窓外）
 → 全ツリー(362)は取得済みなのに AI へ渡さず count() にしか使わない
 → プロンプトは "subset" "truncated" の情報がゼロ
 → rubric が「テストが存在しない -15」等で不在減点を要求
 → AI が「見えない」を「存在しない」に変換
```

**方針：** 「もっと読ませる」のではなく、**「何を読めていないかを AI に認識させる」**。

## アーキテクチャ

3つの独立した器と、それを Claude に届ける契約で構成する。

```
GitHub repository
      │
      ├─ 全 file tree ─────────────▶ RepositoryFacts   (何が存在するか)
      │
      ├─ 選択ファイル + read上限 ──▶ EvidenceCoverage  (何をどこまで読めたか)
      │
      ▼
 Context Contract  (優先順位と推論規則)
      ▼
 Claude Review
```

### 1. RepositoryFacts — 何が存在するか
file tree（paths のみ）から追加 API 無しで deterministic に事実を確定する。核は **3状態契約**：

| status | 意味 |
|---|---|
| `detected` | 存在を確認済み |
| `none` | 十分な探索を行った上での不在を確認済み |
| `unknown` | 確認できていない |

**不変条件：`unknown` を `none`（不在）に変換してはならない。** tree で列挙できる事実（tests / ci）は detected/none、tree だけで判定できない事実（validations 等）は unknown。
`app/Services/RepositoryFacts.php`

### 2. EvidenceCoverage — 何をどこまで読めたか
今回 AI に実際に与えた範囲を保持する。deep-read が全体の **subset** か、各ファイルが **truncated** かを明示する。

```
coverage_mode: subset  (total_files=362 / selected_files=12)
- app/models/user.rb: read_chars=3000 / total_chars=6920 / truncated=true
```
`app/Services/EvidenceCoverage.php`

### 3. Prompt Contract — Claude に「情報の優先順位」を渡す
単に "Test framework: RSpec" と足すのではなく、**何を信頼すべきかの順序**を渡す：

1. **Repository Facts** — 機械的に確認済みの事実（最優先）
2. **Evidence Coverage** — 今回あなたが実際に読めた範囲
3. **Code excerpts** — その範囲から推論する材料

推論規則（`<context_contract>`）：
- `unknown` を `none` として扱わない（`unknown != none`）。
- subset の未選択ファイル／truncated の非表示部分に見えないことを根拠に「存在しない」と断定しない。
- Repository Facts とコード抜粋からの推論が矛盾する場合は Facts を優先する。

`ClaudeReviewService::buildReviewPromptWithContext()` が既存プロンプトを再利用して前置合成する（巨大プロンプトを複製しない）。

## パイプライン接続

`ProcessReviewJob` が既取得の `$tree` と選択ファイル `$files` を渡し、
`ClaudeReviewService::review()` → `buildRepositoryReviewPrompt()` が
`RepositoryFacts::fromTree($tree)` と `EvidenceCoverage::build(count($tree), $files, 3000)` を計算して
context 版プロンプトを送る。

これにより Claude は初めて **「自分は全部を見たわけではない」** と知らされた状態でレビューする。同じ Claude・同じ 3000 文字制限・同じ選択ファイルでも、**渡された情報の意味が変わる**。

## テスト

すべて実 Claude API / 実 GitHub 非依存。RewardMe の2誤検出を最小 fixture で deterministic に再現する（`tests/Unit/`）。

- `RepositoryFactsTest` — 3状態契約・tests detected/rspec/4・validations unknown
- `EvidenceCoverageTest` — subset・truncated（user.rb 3000/6920）
- `ReviewPromptContractTest` — facts/coverage/context_contract がプロンプトに載る・優先順位
- `ReviewPipelineContextTest` — tree/files から facts/coverage を計算して context prompt へ到達

## ステータス

| ステップ | 内容 | 状態 |
|---|---|---|
| 1 | RepositoryFacts（3状態契約） | ✅ done |
| 2 | EvidenceCoverage（subset / truncation） | ✅ done |
| 3 | Prompt Contract（優先順位・不在断定禁止） | ✅ done |
| 3a | Pipeline 配線（review()/ProcessReviewJob） | ✅ done |
| 4 | rubric を facts 連動に（`tests.status==none` のときだけ「テスト無し -15」等） | ⏳ pending |
| 5 | RewardMe 再レビューで 2FP 消滅・TP 維持を検証（Acceptance） | ⏳ pending |

Ref: Issue #1 "Repository Intelligence: distinguish unknown from absent findings"
