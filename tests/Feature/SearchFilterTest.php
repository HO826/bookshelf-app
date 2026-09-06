<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_タイトルキーワードで書籍を検索できる(): void
    {
        $user = User::factory()->create();

        $targetBook = Book::factory()->create(['title' => 'Laravel実践入門']);
        $otherBook = Book::factory()->create(['title' => 'Python超入門']);

        // 検索キーワード「Laravel」でアクセス
        $response = $this->actingAs($user)->get(route('books.index', ['keyword' => 'Laravel']));

        $response->assertStatus(200);
        $response->assertSee('Laravel実践入門');
        $response->assertDontSee('Python超入門');
    }

    public function test_著者名で書籍検索できる(): void
    {
        $user = User::factory()->create();

        $targetBook = Book::factory()->create(['author' => '夏目漱石']);

        $otherBook = Book::factory()->create(['author' => '又吉直樹']);

        $response = $this->actingAs($user)->get(route('books.index', ['keyword' => '夏目漱石']));

        $response->assertStatus(200);
        $response->assertSee('夏目漱石');
        $response->assertDontSee('又吉直樹');
    }

    public function test_ジャンルで書籍を絞り込める(): void
    {
        $user = User::factory()->create();

        $genreA = Genre::factory()->create(['name' => 'プログラミング']);
        $genreB = Genre::factory()->create(['name' => 'デザイン']);

        $bookA = Book::factory()->create(['title' => 'PHP本']);
        $bookA->genres()->attach($genreA->id);

        $bookB = Book::factory()->create(['title' => 'UI本']);
        $bookB->genres()->attach($genreB->id);

        // ジャンルAで絞り込み
        $response = $this->actingAs($user)->get(route('books.index', ['genre' => $genreA->id]));

        $response->assertStatus(200);
        $response->assertSee('PHP本');
        $response->assertDontSee('UI本');
    }
}
