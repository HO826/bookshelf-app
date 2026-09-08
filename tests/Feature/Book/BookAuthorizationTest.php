<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_未ログインユーザーが一覧画面や詳細画面を表示することができるか(): void
    {
        $book = Book::factory()->create();

        $this->get(route('books.index'))->assertStatus(200);

        $this->get(route('books.show', $book))->assertStatus(200);
    }

    public function test_未ログインユーザーは画面アクセス時にログイン画面へリダイレクトされるか(): void
    {
        $book = Book::factory()->create();

        $this->get(route('books.create'))->assertRedirect(route('login'));

        $this->get(route('books.edit', $book))->assertRedirect(route('login'));
    }

    public function test_未ログインユーザーは更新・削除時にログイン画面へリダイレクトされるか(): void
    {
        $book = Book::factory()->create();

        $this->put(route('books.update', $book))->assertRedirect(route('login'));

        $this->delete(route('books.destroy', $book))->assertRedirect(route('login'));
    }

    public function test_他人の書籍の編集画面にアクセスした場合403エラーになるか(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser)->get(route('books.edit', $book));

        $response->assertStatus(403);
    }

    public function test_他人の書籍を更新しようとした場合403エラーになるか(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->for($owner)->create(['title' => '元のタイトル']);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($otherUser)->put(route('books.update', $book), [
            'title' => '改ざんタイトル',
            'author' => $book->author,
            'published_date' => $book->published_date,
            'isbn' => $book->isbn,
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '元のタイトル',
        ]);
    }

    public function test_他人の書籍を削除しようとした場合403エラーになるか(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->for($owner)->create();

        $response = $this->actingAs($otherUser)->delete(route('books.destroy', $book));

        $response->assertStatus(403);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
