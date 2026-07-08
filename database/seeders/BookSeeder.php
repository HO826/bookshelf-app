<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 登録者（山田太郎）の取得
        $user = User::first();

        // 2. ジャンルデータを「名前」をキーにして一括取得
        $genres = Genre::all()->keyBy('name');

        // 3. 要件シート通りの書籍データ配列（11件）
        $books = [
            ['title' => '吾輩は猫である', 'author' => '夏目漱石', 'isbn' => '9784101010014', 'date' => '1905-01-01', 'genre' => '小説', 'num' => 1],
            ['title' => '人を動かす', 'author' => 'D・カーネギー', 'isbn' => '9784422100524', 'date' => '1936-10-01', 'genre' => 'ビジネス, 自己啓発', 'num' => 2],
            ['title' => 'リーダブルコード', 'author' => 'Dustin Boswell', 'isbn' => '9784873115658', 'date' => '2012-06-23', 'genre' => '技術書', 'num' => 3],
            ['title' => '7つの習慣', 'author' => 'スティーブン・R・コヴィー', 'isbn' => '9784863940246', 'date' => '2013-08-30', 'genre' => 'ビジネス, 自己啓発', 'num' => 4],
            ['title' => '坊っちゃん', 'author' => '夏目漱石', 'isbn' => '9784101010021', 'date' => '1906-04-01', 'genre' => '小説', 'num' => 5],
            ['title' => 'サピエンス全史', 'author' => 'ユヴァル・ノア・ハラリ', 'isbn' => '9784309226712', 'date' => '2016-09-08', 'genre' => '歴史, 科学', 'num' => 6],
            ['title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9784048930598', 'date' => '2017-12-18', 'genre' => '技術書', 'num' => 7],
            ['title' => '嫌われる勇気', 'author' => '岸見一郎・古賀史健', 'isbn' => '9784478025819', 'date' => '2013-12-13', 'genre' => '自己啓発', 'num' => 8],
            ['title' => '火花', 'author' => '又吉直樹', 'isbn' => '9784163902302', 'date' => '2015-03-11', 'genre' => '小説', 'num' => 9],
            ['title' => 'FACTFULNESS', 'author' => 'ハンス・ロスリング', 'isbn' => '9784822289607', 'date' => '2019-01-11', 'genre' => 'ビジネス, 科学', 'num' => 10],
            ['title' => 'コンテナ物語', 'author' => 'マルク・レビンソン', 'isbn' => '9784822251468', 'date' => '2007-01-18', 'genre' => 'ビジネス, 歴史', 'num' => 11],
        ];

        // 4. ループ処理で1件ずつデータベースへ登録
        foreach ($books as $data) {
            $book = Book::firstOrCreate(
                [
                    'isbn' => $data['isbn'], // 重複防止のキー
                ],
                [
                    'user_id' => $user->id, // 山田太郎のID
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'published_date' => $data['date'],
                    'description' => $data['title'].'の概要説明文です。', // 任意のテキスト
                    'image_url' => 'https://placeholder.co/200x300/e2e8f0/475569?text='.$data['num'], // 要件のURL形式
                ]
            );

            // ジャンル（中間テーブル）の紐付け処理
            if (isset($genres[$data['genre']])) {
                $genreId = $genres[$data['genre']]->id;
                $book->genres()->sync([$genreId]);
            }
        }
    }
}
