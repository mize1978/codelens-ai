<?php

namespace Tests\Unit;

use App\Services\ClaudeReviewService;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1 / Step 4: rubric 連動。
 *
 * これまでで「AIに正しく考えさせる」までは出来た（Prompt Contract）。だが採点 rubric は
 * まだ静的文言で「テストが存在しない → -15」「入力バリデーションの欠如 → -15」を無条件に
 * 要求している。Prompt Contract が "unknown を none にするな" と言っても、rubric が強く
 * 不在判定を促している状態。
 *
 * Step 4 の目的は「点数も事実に従わせる」こと。rubric の減点条件を Repository Facts /
 * Evidence Coverage に連動させる:
 *   - tests.status = detected → 「テストが存在しない」減点は不可
 *   - tests.status = none     → そのときだけ「テストが存在しない -15」を適用可
 *   - tests.status = unknown  → 減点しない
 *   - validation が truncated / evidence 不足で確認できない → 「欠如」と断定して減点しない
 *   - Facts と rubric が矛盾する場合は Facts 優先
 *
 * rubric 生成を pure（実 API 非依存・deterministic）にテストする。facts/coverage は配列で
 * 渡す（tests=unknown 等、任意の status を注入して固定するため）。
 * 現実装に buildScoringRubric() が無いため、未実装が理由で RED になる。
 */
class ScoringRubricContractTest extends TestCase
{
    private function facts(string $testsStatus, string $validationStatus = 'unknown'): array
    {
        return [
            'tests' => [
                'status'     => $testsStatus,
                'framework'  => $testsStatus === 'detected' ? 'rspec' : null,
                'file_count' => $testsStatus === 'detected' ? 4 : 0,
            ],
            'validations' => [
                'status' => $validationStatus,
                'reason' => 'not_determinable_from_tree',
            ],
        ];
    }

    private function coverage(): array
    {
        return [
            'total_files'    => 362,
            'selected_files' => 2,
            'coverage_mode'  => 'subset',
            'files'          => [
                'app/models/user.rb' => ['total_chars' => 6920, 'read_chars' => 3000, 'truncated' => true],
            ],
        ];
    }

    private function rubric(array $facts): string
    {
        return (new ClaudeReviewService())->buildScoringRubric($facts, $this->coverage());
    }

    /** tests=detected → 「テストが存在しない」減点は不可（blocked） */
    public function test_tests_deduction_is_blocked_when_detected(): void
    {
        $rubric = $this->rubric($this->facts('detected'));

        $this->assertStringContainsString('tests_deduction: blocked', $rubric);
        $this->assertStringContainsString('detected', $rubric);
    }

    /** tests=none のときだけ「テストが存在しない -15」を適用できる（allowed） */
    public function test_tests_deduction_is_allowed_only_when_none(): void
    {
        $rubric = $this->rubric($this->facts('none'));

        $this->assertStringContainsString('tests_deduction: allowed', $rubric);
        $this->assertStringContainsString('-15', $rubric);
    }

    /** tests=unknown → 減点しない（blocked） */
    public function test_tests_deduction_is_blocked_when_unknown(): void
    {
        $rubric = $this->rubric($this->facts('unknown'));

        $this->assertStringContainsString('tests_deduction: blocked', $rubric);
        $this->assertStringContainsString('unknown', $rubric);
    }

    /** validation が確認できない（validations=unknown / 対象 truncated）→ 欠如と断定して減点しない */
    public function test_validation_deduction_is_blocked_when_not_confirmable(): void
    {
        // tests は detected だが、validation は unknown（tree だけでは判定不能・対象は truncated）
        $rubric = $this->rubric($this->facts('detected', 'unknown'));

        $this->assertStringContainsString('validation_deduction: blocked', $rubric);
    }

    /** Facts と rubric が矛盾する場合は Facts を優先する、と明示する */
    public function test_rubric_declares_facts_take_precedence(): void
    {
        $rubric = $this->rubric($this->facts('detected'));

        $this->assertStringContainsString('Repository Facts', $rubric);
        $this->assertMatchesRegularExpression('/優先|precede|override/u', $rubric);
    }
}
