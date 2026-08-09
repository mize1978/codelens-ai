<?php

namespace App\Services;

/**
 * リポジトリの file tree（paths のみ）から、追加 API 無しで「何が存在するか」を
 * deterministic に確定する。AI に渡す前段で「確認できた事実」を組み立てるための土台。
 *
 * 3 状態契約（Issue #1）:
 *   - detected = 存在を確認済み
 *   - none     = 十分な探索を行った結果、不存在を確認済み
 *   - unknown  = 確認できていない（← 絶対に none に変換してはならない）
 *
 * tree は全ファイルを列挙できるため、tree だけで判定できる事実（tests / ci 等）は
 * detected か none になる。tree だけでは判定できない事実（validations 等、本文が必要で
 * 取得は truncated される）は unknown とし、none にしない。
 */
class RepositoryFacts
{
    public const STATUS_DETECTED = 'detected';
    public const STATUS_NONE     = 'none';
    public const STATUS_UNKNOWN  = 'unknown';

    /** 解析対象から除外するノイズディレクトリ */
    private const NOISE_PREFIXES = ['vendor/', 'node_modules/', '.git/', 'dist/', 'build/', 'storage/'];

    /**
     * テストフレームワークの判定規則（フレームワーク非依存・一般規則）。
     * 特定リポジトリ固有のハードコードは持たない。
     */
    private const TEST_MATCHERS = [
        'rspec'   => '#_spec\.rb$#',
        'phpunit' => '#Test\.php$#',
        'jest'    => '#(\.test\.(js|ts|jsx|tsx)$|(^|/)__tests__/)#',
        'pytest'  => '#((^|/)test_[^/]+\.py$|_test\.py$)#',
        'gotest'  => '#_test\.go$#',
    ];

    private function __construct(private array $facts) {}

    public static function fromTree(array $tree): self
    {
        $paths = self::sourcePaths($tree);

        return new self([
            'tests'       => self::detectTests($paths),
            'validations' => [
                'status' => self::STATUS_UNKNOWN,
                'reason' => 'not_determinable_from_tree',
            ],
        ]);
    }

    public function toArray(): array
    {
        return $this->facts;
    }

    /** blob のパスのうち、ノイズディレクトリを除いたもの */
    private static function sourcePaths(array $tree): array
    {
        $paths = [];
        foreach ($tree as $entry) {
            if (($entry['type'] ?? null) !== 'blob') {
                continue;
            }
            $path = (string) ($entry['path'] ?? '');
            if ($path === '') {
                continue;
            }
            foreach (self::NOISE_PREFIXES as $prefix) {
                if (str_starts_with($path, $prefix)) {
                    continue 2;
                }
            }
            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * テストフレームワークと件数を判定する。
     * 一致した matcher があれば detected、どれにも当たらなければ
     * （探索した上での不在として）none を返す。unknown にはしない。
     */
    private static function detectTests(array $paths): array
    {
        foreach (self::TEST_MATCHERS as $framework => $pattern) {
            $count = 0;
            foreach ($paths as $path) {
                if (preg_match($pattern, $path)) {
                    $count++;
                }
            }
            if ($count > 0) {
                return [
                    'status'     => self::STATUS_DETECTED,
                    'framework'  => $framework,
                    'file_count' => $count,
                ];
            }
        }

        return [
            'status'     => self::STATUS_NONE,
            'framework'  => null,
            'file_count' => 0,
        ];
    }
}
