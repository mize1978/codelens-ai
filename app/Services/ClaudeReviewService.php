<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClaudeReviewService
{
    private string $model = 'claude-sonnet-4-6';
    private string $endpoint = 'https://api.anthropic.com/v1/messages';

    public function review(string $owner, string $repo, array $files): array
    {
        $text = $this->call($this->buildReviewPrompt($owner, $repo, $files));
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

        // fallback: 旧形式（コードブロックのみ）
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
あなたはシニアエンジニアのコードレビュアーです。
GitHubリポジトリ「{$owner}/{$repo}」のコードを分析し、以下のJSON形式のみで応答してください。
JSONのみを出力し、マークダウンコードブロック（```）や説明文は含めないでください。

{
  "language": "主要な言語（例: PHP, Ruby, Python）",
  "framework": "使用フレームワーク（例: Laravel, Rails, なし）",
  "quality_score": 0〜100の整数（コード品質）,
  "security_score": 0〜100の整数（セキュリティ）,
  "maintainability_score": 0〜100の整数（保守性）,
  "summary": "リポジトリの概要（1〜2文）",
  "strengths": ["良い点1", "良い点2", "良い点3"],
  "issues": [
    {
      "severity": "critical|warning|suggestion",
      "title": "問題のタイトル",
      "description": "説明",
      "file": "該当ファイル名（不明な場合はnull）"
    }
  ],
  "refactor_suggestions": ["リファクタ提案1", "リファクタ提案2", "リファクタ提案3"],
  "security_notes": ["セキュリティ注意点1", "セキュリティ注意点2"],
  "one_line_verdict": "総評を一言で（辛口OKでユーモアも可）"
}

重要：issueのseverityは必ず critical / warning / suggestion のいずれかを使用してください。

分析対象のファイル：{$fileContents}
PROMPT;
    }

    private function call(string $prompt): string
    {
        $response = Http::withHeaders([
            'x-api-key'         => env('ANTHROPIC_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(120)->post($this->endpoint, [
            'model'      => $this->model,
            'max_tokens' => 2048,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Claude API error: ' . $response->status());
        }

        return $response->json('content.0.text', '');
    }

    private function parseJson(string $text, array $default): array
    {
        // strip markdown fences
        $cleaned = preg_replace('/^```(?:json)?\n?|\n?```$/s', '', trim($text));
        $data = json_decode($cleaned, true);
        if ($data) return $data;

        // fallback: extract first { ... } block
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
            'strengths' => [], 'issues' => [],
            'refactor_suggestions' => [], 'security_notes' => [],
            'one_line_verdict' => 'Parse error',
        ];
    }
}
