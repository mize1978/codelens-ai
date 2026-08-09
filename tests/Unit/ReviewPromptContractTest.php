<?php

namespace Tests\Unit;

use App\Services\ClaudeReviewService;
use App\Services\EvidenceCoverage;
use App\Services\RepositoryFacts;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1 / Step 3: Prompt Contract。
 *
 * Step 1(RepositoryFacts) と Step 2(EvidenceCoverage) で作った「認識の器」を、
 * 初めて Claude のレビュー用プロンプトへ接続する。ここでの狙いは単に
 * "Test framework: RSpec" と足すことではなく、Claude に情報の優先順位を渡すこと:
 *
 *   1. Repository Facts   — 機械的に確認済みの事実（最優先）
 *   2. Evidence Coverage  — 今回 AI が実際にどこまで見たか
 *   3. Code excerpts      — その範囲から推論する材料
 *
 * 実 Claude API は呼ばない。プロンプト文字列を deterministic に検証する。
 * 現実装には facts/coverage をプロンプトへ載せる経路が無いため、
 * buildReviewPromptWithContext() 未実装が理由で RED になる。
 *
 * 責務境界: Step 3 は「情報と推論規則を渡す」だけ。rubric の減点条件（"テストなし -15" 等）を
 * facts 連動に変えるのは Step 4。ここでは rubric の数値・条件は変更しない。
 */
class ReviewPromptContractTest extends TestCase
{
    /** RewardMe を実API非依存で再現した最小 tree（spec 4件・vendor はノイズ） */
    private function rewardmeTree(): array
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

    /** user.rb は 6920 chars（read 上限 3000 で truncated=true になる） */
    private function files(): array
    {
        return [
            'app/models/user.rb'                  => str_repeat('a', 6920),
            'app/controllers/games_controller.rb' => str_repeat('b', 500),
        ];
    }

    private function buildPrompt(): string
    {
        $facts    = RepositoryFacts::fromTree($this->rewardmeTree());
        $coverage = EvidenceCoverage::build(362, $this->files(), 3000);

        return (new ClaudeReviewService())
            ->buildReviewPromptWithContext('mize1978', 'rewardme', $this->files(), $facts, $coverage);
    }

    /** ① Repository Facts がプロンプトに含まれる（tests=detected / rspec） */
    public function test_prompt_includes_repository_facts(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('<repository_facts>', $prompt);
        $this->assertStringContainsStringIgnoringCase('rspec', $prompt);
        $this->assertStringContainsString('detected', $prompt);
    }

    /** ② Evidence Coverage がプロンプトに含まれ、subset であることを認識できる */
    public function test_prompt_includes_evidence_coverage_as_subset(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('<evidence_coverage>', $prompt);
        $this->assertStringContainsString('subset', $prompt);
    }

    /** ③ truncated ファイルには read_chars / total_chars / truncated=true が明示される */
    public function test_prompt_marks_truncated_file_with_char_counts(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('app/models/user.rb', $prompt);
        $this->assertStringContainsString('6920', $prompt);   // total_chars
        $this->assertStringContainsString('3000', $prompt);   // read_chars
        $this->assertStringContainsString('truncated', $prompt);
    }

    /** ④ 情報の優先順位（Facts → Coverage → Code excerpts）がこの順で示される */
    public function test_prompt_declares_information_priority_order(): void
    {
        $prompt = $this->buildPrompt();

        $facts    = mb_strpos($prompt, 'Repository Facts');
        $coverage = mb_strpos($prompt, 'Evidence Coverage');
        $excerpts = mb_strpos($prompt, 'Code excerpts');

        $this->assertNotFalse($facts);
        $this->assertNotFalse($coverage);
        $this->assertNotFalse($excerpts);
        $this->assertTrue($facts < $coverage && $coverage < $excerpts, '優先順位は Facts → Coverage → Code excerpts の順であること');
    }

    /**
     * ⑤ context_contract があり、unknown を surface する（none に潰さない）。
     * ＝未選択/truncated に情報が見えないことを根拠に "存在しない" と断定させない土台。
     */
    public function test_prompt_includes_context_contract_and_surfaces_unknown(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('<context_contract>', $prompt);
        $this->assertStringContainsString('unknown', $prompt);
    }

    /** ⑥ コード抜粋（優先度3）は従来どおり残る＝contextを足すだけで excerpt を消さない */
    public function test_prompt_still_includes_code_excerpts(): void
    {
        $prompt = $this->buildPrompt();

        $this->assertStringContainsString('<file_content>', $prompt);
        $this->assertStringContainsString('app/controllers/games_controller.rb', $prompt);
    }
}
