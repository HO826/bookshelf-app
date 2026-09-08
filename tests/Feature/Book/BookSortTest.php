<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_ソートパラメータなしの場合、デフォルト_latestで表示されるか(): void
    {
        $user = User::factory()->create();

        $oldBook = Book::factory()->create(['created_at' => now()->subDays(2)]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($user)->get(route('books.index', ['sort' => 'latest']));

        $response->assertStatus(200);
        $response->assertSeeInOrder([$newBook->title, $oldBook->title]);
    }

    public function test_書籍一覧を_oldest、登録日が古い順で表示されるか(): void
    {
        $user = User::factory()->create();

        $oldBook = Book::factory()->create(['created_at' => now()->subDays(2)]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($user)->get(route('books.index', ['sort' => 'oldest']));

        $response->assertStatus(200);
        $response->assertSeeInOrder([$oldBook->title, $newBook->title]);
    }

    public function test_書籍一覧を_title昇順で表示されるか(): void
    {
        $user = User::factory()->create();

        $bookA = Book::factory()->create(['title' => 'C言語入門']);

        $bookB = Book::factory()->create(['title' => 'BookShelf開発']);

        $response = $this->actingAs($user)->get(route('books.index', ['sort' => 'title']));

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $bookB->title,
            $bookA->title,
        ]);
    }

    public function test_書籍一覧を評価が高い順にソートされ、未評価の書籍が最後に表示されるか(): void
    {
        $user = User::factory()->create();

        $highRatedBook = Book::factory()->create(['title' => '高評価本']);
        $highRatedBook = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 5,
            'comment' => '最高でした',
        ]);

        $midRatedBook = Book::factory()->create(['title' => '中評価本']);
        $midRatedBook = Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 3,
            'comment' => '普通でした',
        ]);

        $unratedBook = Book::factory()->create(['title' => '未評価本']);

        $response = $this->actingAs($user)->get(route('books.index', ['sort' => 'rating']));

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $highRatedBook->title,
            $midRatedBook->title,
            $unratedBook->title,
        ]);
    }

    public function test_不正なsort値が渡された場合、デフォルト（latest）で表示されるか(): void
    {
        $user = User::factory()->create();

        $oldBook = Book::factory()->create(['created_at' => now()->subDays(2)]);
        $newBook = Book::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($user)->get(route('books.index', ['sort' => 'invalid_value']));

        $response->assertStatus(200);
        $response->assertSeeInOrder([$newBook->title, $oldBook->title]);
    }
}
