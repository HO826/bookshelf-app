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
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        // 日本語テンプレート五段階に変更
        $commentsByRating = [
            1 => '期待していた内容と異なり、あまり参考になりませんでした。',
            2 => '部分的に役立つ箇所もありましたが、全体的に少し物足りない印象です。',
            3 => '可もなく不可もなく、一般的な内容がまとめられている印象でした。',
            4 => 'とても読みやすく、実践的な内容が多くて参考になりました。',
            5 => '内容が非常に素晴らしく、何度も読み返したい名著だと思います！',
        ];

        foreach ($books as $book) {

            // レビュー件数ランダム化
            $reviewCount = rand(2, 4);

            // 同じユーザーが同じ本に重複レビューしないよう、ユーザーのリストをシャッフル
            $shuffledUsers = $users->shuffle()->take($reviewCount);

            foreach ($shuffledUsers as $user) {
                // 評価を1〜5に拡大
                $rating = rand(1, 5);

                Review::create([
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'rating' => $rating,
                    'comment' => $commentsByRating[$rating],
                ]);
            }
        }
    }
}
