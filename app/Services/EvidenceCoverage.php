<?php

namespace App\Services;

/**
 * AI が「自分がどこまで読んだか」を保持する。RepositoryFacts が「何が存在するか」を
 * 表すのに対し、EvidenceCoverage は「今回 AI に実際に与えられた／読めた証拠の範囲」を表す。
 *
 * - deep-read 対象がリポジトリ全体の subset か full かを区別する
 * - ファイルごとに total_chars（全文）と read_chars（実際に渡した文字数）を保持する
 * - read_chars < total_chars なら truncated=true（＝そのファイルから "存在しない" と
 *   断定させないためのメタ）
 *
 * 注意: read 上限は呼び出し側が渡す。既存の 3000 文字制限そのものは変更せず、報告するだけ。
 */
class EvidenceCoverage
{
    public const MODE_SUBSET = 'subset';
    public const MODE_FULL   = 'full';

    private function __construct(private array $coverage) {}

    /**
     * @param int   $totalFiles リポジトリ全体のファイル数
     * @param array $files      [path => 全文コンテンツ]（AI に渡す前の full content）
     * @param int   $readLimit  1ファイルあたり AI に渡す最大文字数（既存の 3000 等）
     */
    public static function build(int $totalFiles, array $files, int $readLimit): self
    {
        $limit = max(0, $readLimit);
        $fileCoverage = [];

        foreach ($files as $path => $content) {
            $total = mb_strlen((string) $content);
            $read  = min($total, $limit);
            $fileCoverage[$path] = [
                'total_chars' => $total,
                'read_chars'  => $read,
                'truncated'   => $read < $total,
            ];
        }

        $selected = count($files);

        return new self([
            'total_files'    => $totalFiles,
            'selected_files' => $selected,
            'coverage_mode'  => $selected < $totalFiles ? self::MODE_SUBSET : self::MODE_FULL,
            'files'          => $fileCoverage,
        ]);
    }

    public function toArray(): array
    {
        return $this->coverage;
    }

    /** そのファイルが truncated（全文を読めていない）か。未知パスは false。 */
    public function isTruncated(string $path): bool
    {
        return $this->coverage['files'][$path]['truncated'] ?? false;
    }
}
