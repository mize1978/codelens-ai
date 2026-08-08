<?php

namespace Tests\Unit;

use App\Services\EvidenceCoverage;
use PHPUnit\Framework\TestCase;

/**
 * Issue #1 / Step 2: Evidence Coverage — AI が「自分がどこまで読んだか」を認識できる契約。
 *
 * 責務境界:
 *   - RepositoryFacts   = リポジトリについて機械的に確認できた事実（何が存在するか）
 *   - EvidenceCoverage  = AI が実際に与えられた／読めた証拠の範囲（何をどこまで見たか）
 *
 * 防ぎたい誤検出（RewardMe 再現）: user.rb は 6920 chars あるのに先頭 3000 chars だけが
 * AI に渡され、257 行目の validation が窓外だったにもかかわらず、AI には「途中までしか
 * 読んでいない」情報が無かった。→ truncated なファイルから "存在しない" と断定させないための
 * メタデータをここで固定する。
 *
 * 注意: RewardMe 固有の値は production code にハードコードしない。read 上限は呼び出し側が
 * 渡す（既存の 3000 文字制限の挙動自体は本ステップでは変更しない・報告するだけ）。
 */
class EvidenceCoverageTest extends TestCase
{
    /** RewardMe の user.rb を模した本文（長さ 6920 のみ再現・値はテスト内で生成） */
    private function userRbContent(): string
    {
        return str_repeat('a', 6920);
    }

    /** deep-read はリポジトリ全体の subset であることを表現できる（total と selected を区別） */
    public function test_deep_read_set_is_marked_as_subset_of_repository(): void
    {
        $coverage = EvidenceCoverage::build(
            totalFiles: 362,
            files: [
                'app/models/user.rb' => $this->userRbContent(),
                'app/models/task.rb' => str_repeat('b', 500),
            ],
            readLimit: 3000,
        )->toArray();

        $this->assertSame(EvidenceCoverage::MODE_SUBSET, $coverage['coverage_mode']);
        $this->assertSame(362, $coverage['total_files']);
        $this->assertSame(2, $coverage['selected_files']);
    }

    /** 全ファイルを読んだ場合は full coverage */
    public function test_full_coverage_when_all_repository_files_are_read(): void
    {
        $coverage = EvidenceCoverage::build(
            totalFiles: 2,
            files: ['a.rb' => 'x', 'b.rb' => 'y'],
            readLimit: 3000,
        )->toArray();

        $this->assertSame(EvidenceCoverage::MODE_FULL, $coverage['coverage_mode']);
    }

    /**
     * truncated 再現（RewardMe user.rb）: read_chars < total_chars なら truncated = true。
     * total_chars=6920 / read_chars=3000 / truncated=true。
     */
    public function test_truncated_file_reports_read_chars_less_than_total(): void
    {
        $coverage = EvidenceCoverage::build(
            totalFiles: 362,
            files: ['app/models/user.rb' => $this->userRbContent()],
            readLimit: 3000,
        )->toArray();

        $file = $coverage['files']['app/models/user.rb'];
        $this->assertSame(6920, $file['total_chars']);
        $this->assertSame(3000, $file['read_chars']);
        $this->assertTrue($file['truncated']);
    }

    /** 全文を読んだ（read_chars == total_chars）ファイルは truncated = false */
    public function test_fully_read_file_is_not_truncated(): void
    {
        $coverage = EvidenceCoverage::build(
            totalFiles: 1,
            files: ['README.md' => str_repeat('c', 120)],
            readLimit: 3000,
        )->toArray();

        $file = $coverage['files']['README.md'];
        $this->assertSame(120, $file['total_chars']);
        $this->assertSame(120, $file['read_chars']);
        $this->assertFalse($file['truncated']);
    }

    /**
     * 「対象情報が存在しない」と断定させないためのメタが取れる:
     * truncated なファイルを識別できる（Step 3 でプロンプトが利用する）。
     */
    public function test_truncated_files_are_discoverable(): void
    {
        $coverage = EvidenceCoverage::build(
            totalFiles: 362,
            files: [
                'app/models/user.rb' => $this->userRbContent(),
                'README.md'          => str_repeat('c', 120),
            ],
            readLimit: 3000,
        );

        $this->assertTrue($coverage->isTruncated('app/models/user.rb'));
        $this->assertFalse($coverage->isTruncated('README.md'));
    }

    /** subset と full の mode は互いに異なる固定値であること（将来の契約破壊を防ぐ） */
    public function test_coverage_mode_values_are_distinct(): void
    {
        $this->assertNotSame(EvidenceCoverage::MODE_SUBSET, EvidenceCoverage::MODE_FULL);
    }
}
