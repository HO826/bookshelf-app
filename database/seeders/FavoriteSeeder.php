<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 全ユーザーと全書籍をデータベースから持ってくる
        $users = User::all();
        $books = Book::all();

        if ($users->isEmpty() || $books->isEmpty()) {
            return;
        }

        // 2. 5人のユーザー全員に対して、1人ずつお気に入りを設定していくループ
        foreach ($users as $user) {

            // 要件：「各ユーザーに3〜5冊のお気に入りを設定」
            // 3、4、5の中から、このユーザーが何冊お気に入り登録するかをランダムで決める
            $favoriteCount = rand(3, 5);

            $randomBooks = $books->random($favoriteCount);

            // book_id の配列を抽出
            $bookIds = $randomBooks->pluck('id')->toArray();

            // 4. 要件：「syncWithoutDetaching を使用」
            // ユーザーと本を紐付ける中間テーブル（favorites）にデータを保存します
            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
