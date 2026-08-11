<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーは書籍にレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => 5,
            'comment' => 'とても素晴らしい本でした！',
        ])->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => 'とても素晴らしい本でした！',
        ]);
    }

    public function test_評価がないとレビューを投稿できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post(route('reviews.store', $book), [
            'rating' => '',
            'comment' => 'コメントのみ',
        ])->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_投稿者本人は自分のレビュー編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('reviews.edit', $review))
            ->assertOk();
    }

    public function test_投稿者本人は自分のレビューを更新できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create([
            'rating' => 3,
            'comment' => '更新前',
        ]);

        $this->actingAs($user)->put(route('reviews.update', $review), [
            'rating' => 4,
            'comment' => '更新後のコメント',
        ])->assertRedirect(route('books.show', $review->book));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '更新後のコメント',
        ]);
    }

    public function test_他人のレビューは更新できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->for($owner)->create();

        $this->actingAs($otherUser)->put(route('reviews.update', $review), [
            'rating' => 1,
            'comment' => '乗っ取りコメント',
        ])->assertStatus(403);
    }

    public function test_投稿者本人は自分のレビューを削除できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();

        $this->actingAs($user)->delete(route('reviews.destroy', $review))->assertRedirect(route('books.show', $review->book));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_他人のレビューは削除できない(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->for($owner)->create();

        $this->actingAs($otherUser)->delete(route('reviews.destroy', $review))->assertStatus(403);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
