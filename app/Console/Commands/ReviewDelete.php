<?php

namespace App\Console\Commands;

use App\Models\Review;
use Illuminate\Console\Command;

/**
 * #7 Public Archive Safety ④:
 * 公開Archiveからレビューを削除する唯一の経路。
 * ログイン機構が無い現状で「誰でも削除」を防ぐため、Web経路は作らず
 * 管理者が Render Shell 等から手動実行する Artisan コマンドのみに限定する。
 */
class ReviewDelete extends Command
{
    protected $signature = 'review:delete {id : 削除するレビューID} {--force : 確認プロンプトをスキップ}';

    protected $description = '公開Archiveからレビューを1件削除する（管理者用・Web経路なし）';

    public function handle(): int
    {
        $id = (int) $this->argument('id');
        $review = Review::find($id);

        if (! $review) {
            $this->error("Review #{$id} が見つかりません。");

            return self::FAILURE;
        }

        $this->line("削除対象: #{$review->id}  {$review->owner}/{$review->repo}  (status={$review->status}, created={$review->created_at})");

        if (! $this->option('force') && ! $this->confirm('このレビューを公開Archiveから完全に削除しますか？')) {
            $this->info('中止しました。');

            return self::SUCCESS;
        }

        $review->delete();
        $this->info("Review #{$id} を公開Archiveから削除しました。");

        return self::SUCCESS;
    }
}
