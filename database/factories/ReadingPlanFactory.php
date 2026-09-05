<?php

namespace Database\Factories;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReadingPlanFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(ReadingPlanStatus::cases());
        $isCompleted = $status === ReadingPlanStatus::Completed;

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'target_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $status->value,
            'completed_at' => $isCompleted ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
        ];
    }
}
