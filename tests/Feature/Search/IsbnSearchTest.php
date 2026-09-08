<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_有効な_isbnで検索した場合、書籍情報が正しく取得できるか(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト用書籍',
                            'authors' => ['テスト著者'],
                            'publishedDate' => '2026-01-01',
                            'description' => 'これはテスト用の概要です。',
                            'imageLinks' => [
                                'thumbnail' => 'https://example.com/sample.jpg',
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get(route('books.isbn', [
            'isbn' => '9784123456789',
        ]));

        $response->assertStatus(200);

        $response->assertJson([
            'title' => 'テスト用書籍',
            'author' => 'テスト著者',
            'description' => 'これはテスト用の概要です。',
            'image_url' => 'https://example.com/sample.jpg',
            'published_date' => '2026-01-01',
        ]);
    }

    public function test_存在しない_isbnで検索した場合、404または該当なしのメッセージが返るか(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.googleapis.com/books/v1/volumes*' => Http::response([
                'totalItems' => 0,
            ], 200),
        ]);

        $response = $this->actingAs($user)->get(route('books.isbn', [
            'isbn' => '9780000000000',
        ]));

        $response->assertStatus(404);
    }

    public function test_不正な_isbnフォーマットの場合バリデーションエラーになるか(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('books.isbn', [
            'isbn' => 'invalid-isbn',
        ]));

        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'ISBNは13桁で入力してください。',
        ]);
    }
}
