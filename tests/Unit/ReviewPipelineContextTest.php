<?php

namespace Tests\Unit;

use App\Services\ClaudeReviewService;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1 / Step 3 (a) 配線: Facts/Coverage が実 pipeline を通って context 版 prompt に到達する。
 *
 * Step 3 の buildReviewPromptWithContext() は「facts と coverage を渡されたら」context prompt を
 * 作れる能力だった。しかし実 pipeline（ProcessReviewJob → review()）はまだ tree すら review() へ
 * 渡しておらず、facts/coverage を計算していない＝context 版 prompt に到達していない。
 *
 * 本テストは pipeline の変換責務（tree → RepositoryFacts、files+readLimit → EvidenceCoverage、
 * → context prompt）を、実 API / 実 GitHub 非依存で deterministic に固定する。
 * 現実装に buildRepositoryReviewPrompt() が無いため、未実装が理由で RED になる。
 *
 * GREEN では: この assembly を実装し、review() が tree/readLimit を受けて本メソッドを使い、
 * ProcessReviewJob が $tree を渡す（配線）。
 */
class ReviewPipelineContextTest extends TestCase
{
    /** getFileTree 相当（全 9 エントリ・spec 4件・vendor はノイズ） */
    private function tree(): array
    {
        return array_map(
            fn (string $path) => ['path' => $path, 'type' => 'blob', 'size' => 500],
            [
                'Gemfile',
                'app/models/user.rb',
                'app/controllers/games_controller.rb',
                'spec/models/user_spec.rb',
                'spec/models/task_spec.rb',
                'spec/services/gacha_service_spec.rb',
                'spec/requests/games_reward_security_spec.rb',
                '.github/workflows/ci.yml',
                'vendor/bundle/ruby/gems/rspec-core/spec/dummy_spec.rb',
            ]
        );
    }

    /** selectKeyFiles で選ばれた 2 ファイルの本文（user.rb は 6920 chars） */
    private function files(): array
    {
        return [
            'app/models/user.rb'                  => str_repeat('a', 6920),
            'app/controllers/games_controller.rb' => str_repeat('b', 500),
        ];
    }

    /** pipeline の raw 入力（tree + files + readLimit）から組み立てる */
    private function buildPrompt(): string
    {
        return (new ClaudeReviewService())->buildRepositoryReviewPrompt(
            'mize1978',
            'rewardme',
            $this->files(),
            $this->tree(),
            3000,
        );
    }

    /** tree から RepositoryFacts が計算され、prompt に載る（渡すのは facts でなく tree） */
    public function test_facts_are_computed_from_tree_and_reach_prompt(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('<repository_facts>', $prompt);
        $this->assertStringContainsStringIgnoringCase('rspec', $prompt);
        $this->assertStringContainsString('detected', $prompt);
    }

    /** files+readLimit から EvidenceCoverage が計算され、subset / total_files=count(tree) が載る */
    public function test_coverage_is_computed_from_files_and_tree_size(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('<evidence_coverage>', $prompt);
        $this->assertStringContainsString('subset', $prompt);
        $this->assertStringContainsString('total_files=9', $prompt);   // = count(tree)
        $this->assertStringContainsString('selected_files=2', $prompt); // = count(files)
    }

    /** readLimit=3000 で user.rb(6920) が truncated として明示される */
    public function test_truncation_is_reported_for_pipeline_files(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('app/models/user.rb', $prompt);
        $this->assertStringContainsString('6920', $prompt);
        $this->assertStringContainsString('3000', $prompt);
        $this->assertStringContainsString('truncated', $prompt);
    }

    /** context contract とコード抜粋の両方が最終 prompt に存在する */
    public function test_pipeline_prompt_has_contract_and_excerpts(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('<context_contract>', $prompt);
        $this->assertStringContainsString('<file_content>', $prompt);
    }
}
