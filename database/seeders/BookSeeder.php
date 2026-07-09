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
            ['title' => '吾輩は猫である', 'author' => '夏目漱石', 'isbn' => '9784101010014', 'date' => '1905-01-01', 'genre' => '小説'],
            ['title' => '人を動かす', 'author' => 'D・カーネギー', 'isbn' => '9784422100524', 'date' => '1936-10-01', 'genre' => 'ビジネス, 自己啓発'],
            ['title' => 'リーダブルコード', 'author' => 'Dustin Boswell', 'isbn' => '9784873115658', 'date' => '2012-06-23', 'genre' => '技術書'],
            ['title' => '7つの習慣', 'author' => 'スティーブン・R・コヴィー', 'isbn' => '9784863940246', 'date' => '2013-08-30', 'genre' => 'ビジネス, 自己啓発'],
            ['title' => '坊っちゃん', 'author' => '夏目漱石', 'isbn' => '9784101010021', 'date' => '1906-04-01', 'genre' => '小説'],
            ['title' => 'サピエンス全史', 'author' => 'ユヴァル・ノア・ハラリ', 'isbn' => '9784309226712', 'date' => '2016-09-08', 'genre' => '歴史, 科学'],
            ['title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9784048930598', 'date' => '2017-12-18', 'genre' => '技術書'],
            ['title' => '嫌われる勇気', 'author' => '岸見一郎・古賀史健', 'isbn' => '9784478025819', 'date' => '2013-12-13', 'genre' => '自己啓発'],
            ['title' => '火花', 'author' => '又吉直樹', 'isbn' => '9784163902302', 'date' => '2015-03-11', 'genre' => '小説'],
            ['title' => 'FACTFULNESS', 'author' => 'ハンス・ロスリング', 'isbn' => '9784822289607', 'date' => '2019-01-11', 'genre' => 'ビジネス, 科学'],
            ['title' => 'コンテナ物語', 'author' => 'マルク・レビンソン', 'isbn' => '9784822251468', 'date' => '2007-01-18', 'genre' => 'ビジネス, 歴史'],
        ];

        // 4. foreach で $index を取得（0から始まる連番）
        foreach ($books as $index => $data) {
            // 画像URL用に、1から始まる数字（1, 2, 3...）を計算
            $num = $index + 1;

            $book = Book::firstOrCreate(
                [
                    'isbn' => $data['isbn'],
                ],
                [
                    'user_id' => $user->id,
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'published_date' => $data['date'],
                    // 'description' => $data['title'] . 'の概要説明文です。',
                    'image_url' => 'https://placeholder.co/200x300/e2e8f0/475569?text='.$num,
                ]
            );

            // 5. カンマ区切りのジャンル（例: "ビジネス, 自己啓発"）を配列に分解
            // explode() で ["ビジネス", "自己啓発"] に分け、さらに前後の余分なスペースを trim() で消します
            $genreNames = array_map('trim', explode(',', $data['genre']));

            $genreIds = [];
            foreach ($genreNames as $name) {
                if (isset($genres[$name])) {
                    $genreIds[] = $genres[$name]->id;
                }
            }

            // 抽出した複数のジャンルIDを一括で中間テーブルに保存
            $book->genres()->sync($genreIds);
        }
    }
}
