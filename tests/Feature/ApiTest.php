<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_公開_apiで書籍一覧を取得できる(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'author', 'isbn', 'published_date'],
                ],
            ]);
    }

    public function test_公開_apiで書籍詳細画面を取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'genres',
                    'reviews',
                ],
            ]);

        $this->getJson('/api/v1/books/99999')
            ->assertStatus(404);
    }

    public function test_公開_apiで書籍を新規登録できる(): void
    {
        $user = User::factory()->create();

        $genre = Genre::factory()->create();

        $response = $this->postJson('/api/v1/books', [
            'user_id' => $user->id,
            'title' => 'Laravelの学習',
            'author' => '山田太郎',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'description' => null,
            'image_url' => null,
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'Laravelの学習',
            'user_id' => $user->id,
        ]);
    }

    public function test_公開_apiで書籍を更新できる(): void
    {
        $book = Book::factory()->create();

        $genre = Genre::factory()->create();

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '更新後タイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => '2026-02-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後タイトル',
        ]);
    }

    public function test_公開_apiで書籍を削除できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }
}
