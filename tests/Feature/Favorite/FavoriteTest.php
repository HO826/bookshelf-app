<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーはお気に入り一覧を表示できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $this->actingAs($user)
            ->get(route('favorites.index'))
            ->assertOk()
            ->assertSee($book->title);
    }

    public function test_未承認ユーザーはお気に入り一覧にアクセスできない(): void
    {
        $this->get(route('favorites.index'))
            ->assertRedirect(route('login'));
    }

    public function test_未登録の書籍をお気に入りに追加できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book))->assertRedirect(route('books.show', $book))->assertSessionHas('success', 'お気に入りを更新しました。');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_登録済みの書籍をお気に入りから解除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $this->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book))
            ->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
