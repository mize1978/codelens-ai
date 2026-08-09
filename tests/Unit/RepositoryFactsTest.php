<?php

namespace Tests\Unit;

use App\Services\RepositoryFacts;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1: Repository Intelligence — distinguish `unknown` from `absent`.
 *
 * RepositoryFacts は GitHub の file tree（paths のみ）から、追加 API 無しで
 * deterministic に「何が存在するか」を 3 状態で確定する契約を持つ:
 *   - detected = 存在を確認済み
 *   - none     = 十分な探索を行った結果、不存在を確認済み
 *   - unknown  = 確認できていない（← 絶対に none に変換してはならない）
 *
 * 本テストは RewardMe で実際に起きた 2 件の誤検出を、実 API / 実リポジトリに
 * アクセスせず最小 fixture で deterministic に再現する。
 */
class RepositoryFactsTest extends TestCase
{
    /**
     * RewardMe（Rails）の tree を模した最小 fixture。
     * - spec/ に *_spec.rb が 4 件（= tests は detected / file_count 4 / framework rspec）
     * - vendor 配下の *_spec.rb はノイズ（カウント対象外であるべき）
     */
    private function rewardmeTree(): array
    {
        return array_map(
            fn (string $path, int $size = 500) => ['path' => $path, 'type' => 'blob', 'size' => $size],
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

    /** テストを一切持たないリポジトリの tree（tests は "none" になるべき） */
    private function treeWithoutTests(): array
    {
        return array_map(
            fn (string $path) => ['path' => $path, 'type' => 'blob', 'size' => 300],
            ['Gemfile', 'app/models/user.rb', 'app/controllers/games_controller.rb']
        );
    }

    /**
     * 誤検出#1 の再現: 「テストコードが存在しない」は誤り。
     * tree から spec/*_spec.rb を検出でき、tests は detected / rspec / 4 になるべき。
     */
    public function test_detects_rspec_tests_from_tree(): void
    {
        $facts = RepositoryFacts::fromTree($this->rewardmeTree())->toArray();

        $this->assertSame(RepositoryFacts::STATUS_DETECTED, $facts['tests']['status']);
        $this->assertSame('rspec', $facts['tests']['framework']);
        $this->assertSame(4, $facts['tests']['file_count']);
    }

    /**
     * 契約の核: unknown は none に変換されない。
     * validations は tree だけでは判定できず（本文は truncated される）、unknown になるべき。
     */
    public function test_validations_are_unknown_not_none_when_not_determinable(): void
    {
        $facts = RepositoryFacts::fromTree($this->rewardmeTree())->toArray();

        $this->assertSame(RepositoryFacts::STATUS_UNKNOWN, $facts['validations']['status']);
        $this->assertNotSame(RepositoryFacts::STATUS_NONE, $facts['validations']['status']);
    }

    /**
     * detected と none の区別: テストが本当に無い tree では none（探索した上での不在）になるべき。
     * unknown ではない（tree は全ファイルを列挙できるため）。
     */
    public function test_reports_none_when_tests_truly_absent(): void
    {
        $facts = RepositoryFacts::fromTree($this->treeWithoutTests())->toArray();

        $this->assertSame(RepositoryFacts::STATUS_NONE, $facts['tests']['status']);
    }

    /** 3 状態は互いに異なる固定値であること（将来の契約破壊を防ぐ） */
    public function test_three_state_contract_values_are_distinct(): void
    {
        $states = [
            RepositoryFacts::STATUS_DETECTED,
            RepositoryFacts::STATUS_NONE,
            RepositoryFacts::STATUS_UNKNOWN,
        ];

        $this->assertCount(3, array_unique($states));
    }
}
