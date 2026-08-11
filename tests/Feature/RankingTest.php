<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストユーザーもランキング画面にアクセスできる(): void
    {
        $this->get(route('ranking.index'))->assertStatus(200);
    }

    public function test_レビューがない書籍は表示されない(): void
    {
        $user = User::factory()->create();

        // レビューありの書籍
        $reviewedBook = Book::factory()->create(['title' => 'レビューありの書籍']);
        Review::factory()->create([
            'book_id' => $reviewedBook->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        // レビューなしの書籍
        $noReviewBook = Book::factory()->create(['title' => 'レビューなしの書籍']);

        $response = $this->get(route('ranking.index'));

        $response->assertStatus(200);
        $response->assertSee('レビューありの書籍');
        $response->assertDontSee('レビューなしの書籍');
    }

    public function test_レビュー平均評価のTOP10書籍が降順で表示される(): void
    {
        $user = User::factory()->create();

        // 11冊の書籍を作成し、評価を変えてレビューを投稿（評価 1 〜 12 のイメージ）
        for ($i = 1; $i <= 11; $i++) {
            $book = Book::factory()->create([
                'title' => "書籍{$i}",
            ]);

            Review::factory()->create([
                'book_id' => $book->id,
                'user_id' => $user->id,
                'rating' => (($i - 1) % 5) + 1,
            ]);
        }

        $this->get(route('ranking.index'))->assertStatus(200)
            ->assertViewHas('rankedBooks', function ($rankedBooks) {

                // ランキングが10冊だけになっているか
                if ($rankedBooks->count() !== 10) {
                    return false;
                }

                // 評価が高い順に並んでいるか
                for ($i = 0; $i < $rankedBooks->count() - 1; $i++) {
                    if (
                        $rankedBooks[$i]->reviews_avg_rating
                        < $rankedBooks[$i + 1]->reviews_avg_rating
                    ) {
                        return false;
                    }
                }

                return true;
            });
    }
}
