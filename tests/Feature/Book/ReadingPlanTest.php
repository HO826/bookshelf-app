<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーは読書計画一覧を表示できる(): void
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->count(2)->for($user)->create();

        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertStatus(200);
    }

    public function test_未認証ユーザーは読書計画一覧にアクセスできずログインへリダイレクトされる(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_ログインユーザーは読書計画作成画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertStatus(200);
    }

    public function test_ログインユーザーは読書計画を作成できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => '2026-12-31',
        ]);
    }

    public function test_所有者は読書計画編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $plan));

        $response->assertStatus(200);
    }

    public function test_所有者は読書計画を削除できる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $plan));

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $plan->id,
        ]);
    }

    public function test_所有者は読書計画を読了（完了）にできる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->for($user)->create([
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $plan));

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'completed',
        ]);
    }

    public function test_他人の読書計画の編集・更新・削除・読了は403エラーになる(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->for($owner)->create();

        $this->actingAs($otherUser)
            ->get(route('reading-plans.edit', $plan))
            ->assertStatus(403);

        $this->actingAs($otherUser)
            ->put(route('reading-plans.update', $plan), ['target_date' => '2026-12-31'])
            ->assertStatus(403);

        $this->actingAs($otherUser)
            ->delete(route('reading-plans.destroy', $plan))
            ->assertStatus(403);

        $this->actingAs($otherUser)
            ->post(route('reading-plans.complete', $plan))
            ->assertStatus(403);
    }

    public function test_読書計画の目標期日（期限）を更新できる(): void
    {
        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->for($user)->create([
            'target_date' => '2026-10-01',
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $plan), [
            'target_date' => '2026-11-30',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => '2026-11-30',
        ]);
    }

    public function test_過去の日付には期限変更できない(): void
    {
        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->for($user)->create([
            'target_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $plan), [
            'target_date' => now()->subDays(1)->format('Y-m-d'),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('target_date');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => now()->format('Y-m-d'),
        ]);
    }
}
