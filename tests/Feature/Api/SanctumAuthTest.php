<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_未認証（トークンなし）ユーザーが書き込み_ap_iを実行した場合、401エラーになるか(): void
    {
        $book = Book::factory()->create();

        $this->postJson(route('api.books.store'), [])->assertStatus(401);
        $this->putJson(route('api.books.update', $book), [])->assertStatus(401);
        $this->deleteJson(route('api.books.destroy', $book))->assertStatus(401);
    }

    public function test_sanctum認証されたユーザーは_ap_i経由で書籍を作成できるか(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        Sanctum::actingAs($user);

        $requestData = [
            'title' => 'Sanctumテスト書籍',
            'author' => 'テスト著者',
            'isbn' => '9784123456789',
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->postJson(route('api.books.store'), $requestData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('books', [
            'title' => 'Sanctumテスト書籍',
            'user_id' => $user->id,
        ]);
    }

    public function test_sanctum認証された所有者は_ap_i経由で書籍を更新できるか(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $updateData = [
            'title' => '更新後のタイトル',
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => '2026-01-01',
            'genres' => [$genre->id],
        ];

        $response = $this->putJson(route('api.books.update', $book), $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '更新後のタイトル',
        ]);
    }

    public function test_sanctum認証された所有者は_ap_i経由で書籍を削除できるか(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $response = $this->deleteJson(route('api.books.destroy', $book));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }
}
