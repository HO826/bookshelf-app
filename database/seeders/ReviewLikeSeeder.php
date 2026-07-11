<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 全ユーザー（5人）と 全レビュー（32件）をデータベースから持ってくる
        $users = User::all();
        $reviews = Review::all();

        if ($users->isEmpty() || $reviews->isEmpty()) {
            return;
        }

        // 2. 32件のレビューに対して、1件ずついいねを配分していくループ
        foreach ($reviews as $review) {

            // 要件：「各レビューに0〜3人のユーザーがいいね」
            // 0, 1, 2, 3 の中から、このレビューに何人がいいねするかをランダムで決める
            $likeCount = rand(0, 3);

            // もしランダムで「0人」が選ばれたら、このレビューには何もせず次のレビューの処理へ進む
            if ($likeCount === 0) {
                continue;
            }

            // 要件：「自分のレビューを除く」
            // 全ユーザーの中から「このレビューを書いた本人（$review->user_id）」を除外したメンバーのIDリストを作る
            $eligibleUserIds = $users->where('id', '!=', $review->user_id)->pluck('id');

            // 残った安全なユーザーIDの中から、上で決めた人数分（1〜3人）をランダムにチョイスする
            // ※もし除外した結果、決められた人数より少なくなってしまった場合のエラーを防ぐため、実際のリストの数を超えないように min() で調整します
            $chosenUserIds = $eligibleUserIds->random(min($likeCount, $eligibleUserIds->count()))->toArray();

            // 3. 要件：「syncWithoutDetaching を使用」
            // レビューに対して、選ばれたユーザーのIDを中間テーブル（review_likes）に保存します
            $review->likedByUsers()->syncWithoutDetaching($chosenUserIds);
        }
    }
}
