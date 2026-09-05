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
        // $user = User::first();
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        $genres = Genre::all()->keyBy('name');

        $books = [
            ['title' => '吾輩は猫である', 'author' => '夏目漱石', 'isbn' => '9784101010014', 'published_date' => '1905-01-01', 'genre' => '小説', 'description' => '猫の視点から人間の滑稽な日常や社会をユーモラスに風刺した、夏目漱石の不朽の名作小説。'],
            ['title' => '人を動かす', 'author' => 'D・カーネギー', 'isbn' => '9784422100524', 'published_date' => '1936-10-01', 'genre' => 'ビジネス, 自己啓発', 'description' => '人間関係の原則や他人の心を動かす行動指針を説いた、世界中で読み継がれる自己啓発のバイブル。'],
            ['title' => 'リーダブルコード', 'author' => 'Dustin Boswell', 'isbn' => '9784873115658', 'published_date' => '2012-06-23', 'genre' => '技術書', 'description' => '美しく、理解しやすく、メンテナンスしやすい「良いコード」を書くための実践的テクニック集。'],
            ['title' => '7つの習慣', 'author' => 'スティーブン・R・コヴィー', 'isbn' => '9784863940246', 'published_date' => '2013-08-30', 'genre' => 'ビジネス, 自己啓発', 'description' => '真の成功と幸福を手に入れるために、人格を磨き良好な人間関係を築くための不変の原則。'],
            ['title' => '坊っちゃん', 'author' => '夏目漱石', 'isbn' => '9784101010021', 'published_date' => '1906-04-01', 'genre' => '小説', 'description' => '正義感が強く一本気な主人公が、四国の旧制中学校で繰り広げる人間模様を描いた痛快小説。'],
            ['title' => 'サピエンス全史', 'author' => 'ユヴァル・ノア・ハラリ', 'isbn' => '9784309226712', 'published_date' => '2016-09-08', 'genre' => '歴史, 科学', 'description' => 'ホモ・サピエンスが文明を築き、地球の支配者となった歴史の謎をダイナミックに解き明かす一冊。'],
            ['title' => 'Clean Code', 'author' => 'Robert C. Martin', 'isbn' => '9784048930598', 'published_date' => '2017-12-18', 'genre' => '技術書', 'description' => 'チーム開発を円滑にし、バグを減らすための「クリーンなコード」の書き方と設計のベストプラクティス。'],
            ['title' => '嫌われる勇気', 'author' => '岸見一郎・古賀史健', 'isbn' => '9784478025819', 'published_date' => '2013-12-13', 'genre' => '自己啓発', 'description' => 'アドラー心理学の教えを青年と哲人の対話形式で分かりやすく紐解き、自由に行きる知恵を授ける書。'],
            ['title' => '火花', 'author' => '又吉直樹', 'isbn' => '9784163902302', 'published_date' => '2015-03-11', 'genre' => '小説', 'description' => '売れないお笑い芸人たちの葛藤と純粋な情熱、そして先輩後輩の絆をリアルに描いた芥川賞受賞作。'],
            ['title' => 'FACTFULNESS', 'author' => 'ハンス・ロスリング', 'isbn' => '9784822289607', 'published_date' => '2019-01-11', 'genre' => 'ビジネス, 科学', 'description' => 'データに基づき、思い込みを排除して世界を正しく見るための「事実に基づく世界の見方」を授ける書。'],
            ['title' => 'コンテナ物語', 'author' => 'マルク・レビンソン', 'isbn' => '9784822251468', 'published_date' => '2007-01-18', 'genre' => 'ビジネス, 歴史', 'description' => '「コンテナ」という世界を変えた世紀の発明が、物流や世界経済をどのように激変させたかを追う歴史ノンフィクション。'],
        ];

        foreach ($books as $index => $data) {
            $num = $index + 1;

            $book = Book::firstOrCreate(
                [
                    'isbn' => $data['isbn'],
                ],
                [
                    // 'user_id' => $user->id,
                    'user_id' => $users->random()->id,
                    'title' => $data['title'],
                    'author' => $data['author'],
                    'published_date' => $data['published_date'],
                    'description' => $data['description'],
                    'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text='.$num,
                ]
            );

            $genreNames = array_map('trim', explode(',', $data['genre']));

            $genreIds = [];
            foreach ($genreNames as $name) {
                if (isset($genres[$name])) {
                    $genreIds[] = $genres[$name]->id;
                }
            }

            $book->genres()->sync($genreIds);
        }
    }
}
