<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 関連するUserを自動作成して紐付け
            'user_id' => User::factory(),
            'title' => fake()->realText(15),
            'author' => fake()->name(),
            'isbn' => fake()->isbn13(),
            'published_date' => fake()->date(),
            // 任意項目（nullを許可するカラム）がある場合は optional() を活用
            'description' => fake()->optional()->realText(50),
        ];
    }
}
