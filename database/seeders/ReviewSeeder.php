<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 全ユーザー（5人）と 全書籍（11冊）をデータベースから持ってくる
        $users = User::all();
        $books = Book::all();

        // データの存在チェック（安全対策）
        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        // 2. 要件「具体的なコメント内容」を満たすための、使い回せるコメントのバリエーション
        $comments = [
            'とても読みやすくて勉強になりました。何度も読み返したい一冊です。',
            '内容が深く、新しい発見がたくさんありました。知人にも勧めたいです。',
            '難しそうなテーマですが、初心者にもわかりやすく丁寧に解説されていました。',
            '期待通りの素晴らしい内容でした。この著者の他の本も読んでみたいです。',
        ];

        // 全体の合計レビュー数をカウントするための変数（32件ぴったりにする）
        $totalReviews = 0;
        $maxReviews = 32;

        // 3. 11冊の本に対して、1冊ずつレビューを配分していくループ
        foreach ($books as $bookIndex => $book) {

            // 本ごとに「2件〜4件」のレビュー数を割り振る（最後の本で32件ぴったりに調整）
            if ($bookIndex === $books->count() - 1) {
                $reviewCount = $maxReviews - $totalReviews; // 最後の本は残り物すべて
            } else {
                // 1冊あたり2〜4件をランダム、またはバランスよく（ここでは3件前後を基準に）設定
                $reviewCount = ($bookIndex % 3 === 0) ? 4 : (($bookIndex % 2 === 0) ? 2 : 3);
            }

            // 安全策：配分数が2〜4件の範囲を超えないようにガード（データ増減時の考慮）
            $reviewCount = max(2, min(4, $reviewCount));

            // 残り必要件数を超えないように調整
            if ($totalReviews + $reviewCount > $maxReviews) {
                $reviewCount = $maxReviews - $totalReviews;
            }

            $totalReviews += $reviewCount;

            // 4. その本に対して決まった件数分、レビューを登録する
            for ($i = 0; $i < $reviewCount; $i++) {

                // 投稿するユーザーを5人の中からバランスよく選ぶ（本の番号とループの番号でズラす）
                $user = $users[($bookIndex + $i) % $users->count()];

                // 要件「createを使用する」
                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'comment' => $comments[($bookIndex + $i) % count($comments)], // コメントを順番に選択
                    'rating' => rand(3, 5), // 要件「ratingは3〜5の範囲」
                ]);
            }
        }
    }
}
