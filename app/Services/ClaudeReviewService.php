<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ClaudeReviewService
{
    private string $apiKey;
    private string $model = 'claude-sonnet-4-6';
    private string $endpoint = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = env('ANTHROPIC_API_KEY', '');
    }

    public function review(string $owner, string $repo, array $files): array
    {
        $prompt = $this->buildPrompt($owner, $repo, $files);

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
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

        $text = $response->json('content.0.text', '');
        return $this->parseResponse($text);
    }

    private function buildPrompt(string $owner, string $repo, array $files): string
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
    {"severity": "high|medium|low", "title": "問題のタイトル", "description": "説明"}
  ],
  "refactor_suggestions": ["リファクタ提案1", "リファクタ提案2", "リファクタ提案3"],
  "security_notes": ["セキュリティ注意点1", "セキュリティ注意点2"],
  "one_line_verdict": "総評を一言で（辛口OKでユーモアも可）"
}

分析対象のファイル：{$fileContents}
PROMPT;
    }

    private function parseResponse(string $text): array
    {
        $cleaned = preg_replace('/^```(?:json)?\n?|\n?```$/s', '', trim($text));
        $data = json_decode($cleaned, true);

        if (!$data) {
            return [
                'language' => 'Unknown',
                'framework' => 'Unknown',
                'quality_score' => 50,
                'security_score' => 50,
                'maintainability_score' => 50,
                'summary' => 'レビューの解析に失敗しました。',
                'strengths' => [],
                'issues' => [],
                'refactor_suggestions' => [],
                'security_notes' => [],
                'one_line_verdict' => 'Parse error',
            ];
        }

        return $data;
    }
}
