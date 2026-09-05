<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $mainUser = User::where('email', 'yamada@example.com')->first()
            ?? User::factory()->create(['email' => 'yamada@example.com']);

        $otherUser = User::where('email', 'suzuki@example.com')->first()
            ?? User::factory()->create(['email' => 'suzuki@example.com']);

        $books = Book::limit(4)->get();
        if ($books->count() < 4) {
            $books = Book::factory()->count(4)->create();
        }

        $today = Carbon::today();

        // 計画中
        ReadingPlan::factory()->create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(0)->id,
            'status' => ReadingPlanStatus::Planned,
            'target_date' => $today->copy()->addDays(14),
            'completed_at' => null,
        ]);

        // 読書中
        ReadingPlan::factory()->create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(1)->id,
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => $today->copy(),
            'completed_at' => null,
        ]);

        // 完了
        ReadingPlan::factory()->create([
            'user_id' => $mainUser->id,
            'book_id' => $books->get(2)->id,
            'status' => ReadingPlanStatus::Completed,
            'target_date' => $today->copy()->subDays(5),
            'completed_at' => $today->copy()->subDays(2),
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $books->get(3)->id,
            'status' => ReadingPlanStatus::Planned,
            'target_date' => $today->copy()->addDays(10),
            'completed_at' => null,
        ]);
    }
}
