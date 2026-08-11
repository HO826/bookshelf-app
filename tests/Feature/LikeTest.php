<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_未ログインユーザーはいいねできない(): void
    {
        $review = Review::factory()->create();

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseEmpty('review_likes');
    }

    public function test_ログインユーザーはいいねをトグルできる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        // いいね追加
        $response1 = $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $response1->assertStatus(302);
        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // いいね解除
        $response2 = $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $response2->assertStatus(302);
        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
