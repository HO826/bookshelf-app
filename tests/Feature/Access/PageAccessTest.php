<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_未ログインで書籍一覧画面を表示できること(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_未ログインで書籍詳細画面を表示できること(): void
    {
        $book = Book::factory()->create();

        $this->get("/books/{$book->id}")->assertStatus(200);
    }

    public function test_未ログインでランキング画面を表示できること(): void
    {
        $this->get('/ranking')->assertStatus(200);
    }

    public function test_未ログイン時にログイン必須画面にアクセスするとリダイレクトされること(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);
        $genre = Genre::factory()->create();
        $review = Review::factory()->create();

        $protectedUrls = [
            '/books/create',
            "/reviews/{$review->id}/edit",
            "/books/{$book->id}/edit",
            '/favorites',
            '/genres',
            "/genres/{$genre->id}",
            '/genres/create',
            "/genres/{$genre->id}/edit",
        ];

        foreach ($protectedUrls as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_ログイン済みユーザーが各画面を表示できること(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get('/books/create')->assertStatus(200);
        $this->actingAs($user)->get('/favorites')->assertStatus(200);
        $this->actingAs($user)->get('/genres')->assertStatus(200);
        $this->actingAs($user)->get('/genres/create')->assertStatus(200);

        $this->actingAs($user)->get("/books/{$book->id}")->assertStatus(200);
        $this->actingAs($user)->get("/genres/{$genre->id}")->assertStatus(200);
        $this->actingAs($user)->get("/genres/{$genre->id}/edit")->assertStatus(200);
    }

    public function test_作成者本人のみが書籍およびレビューの編集画面を表示できること(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create(['user_id' => $owner->id]);
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->get("/books/{$book->id}/edit")->assertStatus(200);
        $this->actingAs($owner)->get("/reviews/{$review->id}/edit")->assertStatus(200);

        $this->actingAs($otherUser)->get("/books/{$book->id}/edit")->assertStatus(403);
        $this->actingAs($otherUser)->get("/reviews/{$review->id}/edit")->assertStatus(403);
    }

    public function test_ログイン済みユーザーがログイン画面にアクセスすると一覧へリダイレクトされること(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect('/');
    }
}
