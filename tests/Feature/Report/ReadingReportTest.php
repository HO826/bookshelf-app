<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_未ログインユーザーは読書レポート画面にアクセスできない(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログインユーザーは読書レポート画面を表示できる(): void
    {
        $user = User::factory()->create();

        Book::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);
    }

    public function test_ログインユーザーの読書レポート統計が正しく表示される(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $book1 = Book::factory()->create(['user_id' => $user->id]);
        $book2 = Book::factory()->create(['user_id' => $user->id]);

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 5]);

        $otherBook = Book::factory()->create(['user_id' => $otherUser->id]);
        Review::factory()->create(['user_id' => $otherUser->id, 'book_id' => $otherBook->id, 'rating' => 1]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertStatus(200);

        $response->assertSee('4.5');
        $response->assertSee('2');
    }
}
