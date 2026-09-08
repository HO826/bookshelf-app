<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証ユーザーは書籍一覧を表示できる(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(2)->create();

        $this->actingAs($user)
            ->get(route('books.index'))
            ->assertOk();
    }

    public function test_認証ユーザーは書籍を作成しジャンルを付けられる(): void
    {
        $user = User::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'Laravelの学習',
            'author' => '山田太郎',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => $genres->pluck('id')->toArray(),
        ]);

        $book = Book::where('title', 'Laravelの学習')->first();
        $this->assertNotNull($book);

        $response->assertRedirect(route('books.index', $book));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'user_id' => $user->id,
            'title' => 'Laravelの学習',
        ]);

        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genre', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    public function test_タイトルがないと書籍を作成できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)->post(route('books.store'), [
            'title' => '',
            'author' => '山田太郎',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ])->assertSessionHasErrors('title');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_書籍詳細を表示できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => '詳細テスト']);

        $this->actingAs($user)
            ->get(route('books.show', $book))
            ->assertOk()
            ->assertSee('詳細テスト');
    }

    public function test_所有者は自分の書籍を更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->for($user)->create();

        $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-02-01',
            'genres' => [$genre->id],
        ])->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
            'author' => '更新後著者',
        ]);
    }

    public function test_所有者は自分の書籍を削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('books.destroy', $book))
            ->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    public function test_他人の書籍は更新できない(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => '更新後タイトル',
            'author' => '更新後著者',
            'isbn' => $book->isbn,
            'published_date' => '2026-02-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);
    }
}
