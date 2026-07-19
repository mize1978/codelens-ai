<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClaudeReviewService
{
    private string $model    = 'claude-sonnet-4-6';
    private string $endpoint = 'https://api.anthropic.com/v1/messages';
    public float $lastLatency = 0.0;

    public function review(string $owner, string $repo, array $files): array
    {
        $system = <<<SYS
あなたはシニアソフトウェアエンジニアです。コードレビューの専門家として、具体的・実用的なフィードバックを提供してください。

【必ず守るルール】
- "良い設計です"のような抽象的な評価は禁止。具体的なファイル名・関数名・パターン名で理由を示す
- strengths は「何が・なぜ良いのか」をセットで書く（例: "app/Models/User.rb の validates メソッドが網羅的で、ユーザー入力の安全性を担保している"）
- top_priorities は「今日中に対応すべき」具体的なアクション3つ。ファイル名と対応内容を明示する
- issues の description は「何が問題か」と「なぜ危険/悪いか」を両方含める
- one_line_verdict はそのリポジトリの最大の特徴または最大のリスクに言及する（辛口・ユーモアOK）
SYS;

        $text = $this->call($this->buildReviewPrompt($owner, $repo, $files), $system);
        return $this->parseJson($text, $this->defaultReview());
    }

    public function fixIssue(string $issueTitle, string $issueDesc, array $files): array
    {
        $fileContents = '';
        foreach ($files as $path => $content) {
            $fileContents .= "\n\n### {$path}\n```\n" . mb_substr($content, 0, 4000) . "\n```";
        }

        $prompt = <<<PROMPT
以下の問題について、修正前・修正後・差分を提示してください。
JSONのみで応答してください（マークダウンコードブロック不要）。

{
  "before": "問題のあるコードスニペット（10〜25行）",
  "after": "修正後のコードスニペット（同程度の行数）",
  "diff": "統一diff形式。変更行は先頭に - または + を付け、文脈行は先頭にスペース。例:\n  SECRET_KEY_BASE=dummy\n- RUN bundle exec rails assets:precompile\n+ ARG SECRET_KEY_BASE\n+ RUN SECRET_KEY_BASE=\$SECRET_KEY_BASE bundle exec rails assets:precompile",
  "score_delta": estimated improvement to overall score (integer 1-15) if this fix is applied,
  "explanation": "修正のポイントを1〜2文で"
}

**問題:** {$issueTitle}
**詳細:** {$issueDesc}

**対象ファイル:**{$fileContents}
PROMPT;

        $text = $this->call($prompt);
        $data = $this->parseJson($text, []);

        if (!empty($data['before']) && !empty($data['after'])) {
            return $data;
        }

        return ['before' => null, 'after' => $text, 'explanation' => null];
    }

    private function buildReviewPrompt(string $owner, string $repo, array $files): string
    {
        $fileContents = '';
        foreach ($files as $path => $content) {
            $preview = mb_substr($content, 0, 3000);
            $fileContents .= "\n\n### {$path}\n```\n{$preview}\n```";
        }

        return <<<PROMPT
GitHubリポジトリ「{$owner}/{$repo}」のコードを分析し、以下のJSON形式のみで応答してください。
JSONのみを出力し、マークダウンコードブロック（```）や説明文は含めないでください。

{
  "language": "主要な言語（例: PHP, Ruby, Python）",
  "framework": "使用フレームワーク（例: Laravel, Rails, なし）",
  "quality_score": 0〜100の整数（コード品質）,
  "security_score": 0〜100の整数（セキュリティ）,
  "maintainability_score": 0〜100の整数（保守性）,
  "summary": "リポジトリの技術的概要（1〜2文。言語・フレームワーク・主な機能を含める）",
  "top_priorities": [
    "①（具体的なファイル名）の（具体的な問題）を（具体的なアクション）する",
    "②（具体的なファイル名）の（具体的な問題）を（具体的なアクション）する",
    "③（具体的なファイル名）の（具体的な問題）を（具体的なアクション）する"
  ],
  "strengths": [
    "（具体的なファイル名または設計パターン）が（なぜ良いか）",
    "（具体的なファイル名または設計パターン）が（なぜ良いか）",
    "（具体的なファイル名または設計パターン）が（なぜ良いか）"
  ],
  "issues": [
    {
      "severity": "critical|warning|suggestion",
      "title": "問題のタイトル",
      "description": "何が問題か＋なぜ危険/悪いかを両方含める",
      "file": "該当ファイル名（不明な場合はnull）"
    }
  ],
  "refactor_suggestions": ["リファクタ提案1", "リファクタ提案2", "リファクタ提案3"],
  "security_notes": ["セキュリティ注意点1", "セキュリティ注意点2"],
  "one_line_verdict": "そのリポジトリの最大の特徴またはリスクに言及した総評（辛口・ユーモアOK）"
}

重要：
- issueのseverityは必ず critical / warning / suggestion のいずれかを使用
- top_priorities は criticalまたはwarningの中から最も影響度の高い3つを選ぶ
- 抽象的な評価（"良い設計"など）は禁止。必ず具体的なファイル名・関数名を含める

分析対象のファイル：{$fileContents}
PROMPT;
    }

    private function call(string $prompt, ?string $system = null): string
    {
        $t0 = microtime(true);

        $body = [
            'model'      => $this->model,
            'max_tokens' => 16384,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ];
        if ($system !== null) {
            $body['system'] = $system;
        }

        $response = Http::withHeaders([
            'x-api-key'         => env('ANTHROPIC_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(180)->post($this->endpoint, $body);

        $this->lastLatency = round(microtime(true) - $t0, 2);

        if (!$response->successful()) {
            throw new \RuntimeException('Claude API error: ' . $response->status());
        }

        return $response->json('content.0.text', '');
    }

    private function parseJson(string $text, array $default): array
    {
        $cleaned = preg_replace('/^```(?:json)?\n?|\n?```$/s', '', trim($text));
        $data = json_decode($cleaned, true);
        if ($data) return $data;

        if (preg_match('/\{.+\}/s', $cleaned, $m)) {
            $data = json_decode($m[0], true);
            if ($data) return $data;
        }

        \Log::warning('ClaudeReviewService: JSON parse failed', ['raw' => mb_substr($text, 0, 500)]);
        return $default;
    }

    private function defaultReview(): array
    {
        return [
            'language' => 'Unknown', 'framework' => 'Unknown',
            'quality_score' => 50, 'security_score' => 50, 'maintainability_score' => 50,
            'summary' => 'レビューの解析に失敗しました。',
            'top_priorities' => [], 'strengths' => [], 'issues' => [],
            'refactor_suggestions' => [], 'security_notes' => [],
            'one_line_verdict' => 'Parse error',
        ];
    }
}
