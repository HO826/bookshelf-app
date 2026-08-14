<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーは書籍一覧を表示できる(): void
    {
        // 1. 準備: テスト用ユーザーを作る
        $user = User::factory()->create();
        Book::factory()->create([
            'user_id' => $user->id,
        ]);

        // 2. 実行: 一覧にアクセス
        $response = $this->actingAs($user)->get(route('books.index'));

        // 3. 検証: 200 OK
        $response->assertOk();
    }
}
