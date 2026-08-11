<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    /* ===================================================
     * 1. 画面表示のテスト
     * =================================================== */

    public function test_認証ユーザーはジャンル一覧を表示できる(): void
    {
        $user = User::factory()->create();
        Genre::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('genres.index'))
            ->assertOk();
    }

    public function test_認証ユーザーはジャンル詳細を表示できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => 'SF']);

        $this->actingAs($user)
            ->get(route('genres.show', $genre))
            ->assertOk()
            ->assertSee('SF');
    }

    public function test_認証ユーザーはジャンル作成画面を表示できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('genres.create'))
            ->assertOk();
    }

    public function test_認証ユーザーはジャンル編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)
            ->get(route('genres.edit', $genre))
            ->assertOk();
    }

    /* ===================================================
     * 2. STORE (登録) のテスト
     * =================================================== */

    public function test_認証ユーザーはジャンルを新規登録できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('genres.store'), [
            'name' => 'ファンタジー',
        ])->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを登録しました');

        $this->assertDatabaseHas('genres', [
            'name' => 'ファンタジー',
        ]);
    }

    public function test_ジャンル名がないと登録できない(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('genres.store'), [
            'name' => '',
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('genres', 0);
    }

    /* ===================================================
     * 3. UPDATE (更新) のテスト
     * =================================================== */

    public function test_認証ユーザーはジャンルを更新できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create(['name' => '旧ジャンル']);

        $this->actingAs($user)->put(route('genres.update', $genre), [
            'name' => '新ジャンル',
        ])->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを更新しました');

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '新ジャンル',
        ]);
    }

    /* ===================================================
     * 4. DELETE (削除) のテスト
     * =================================================== */

    public function test_書籍が紐付いていないジャンルは削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $this->actingAs($user)->delete(route('genres.destroy', $genre))->assertRedirect(route('genres.index'))
            ->assertSessionHas('success', 'ジャンルを削除しました。');

        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    public function test_書籍が紐付いているジャンルは削除できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();

        // ジャンルに書籍を紐付ける
        $genre->books()->attach($book->id);

        $this->actingAs($user)->delete(route('genres.destroy', $genre))->assertRedirect(route('genres.index'))
            ->assertSessionHas('error', '書籍が登録されているジャンルは削除できません');

        // DBにデータが残っていること
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
        ]);
    }
}
