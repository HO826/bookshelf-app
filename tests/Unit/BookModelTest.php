<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍のリレーションが正しく動作すること(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $reviewer = User::factory()->create();

        $book = Book::factory()
            ->for($user)
            ->create();

        $genreIds = [$genre->id];
        $book->genres()->sync($genreIds);

        $review = Review::factory()
            ->for($book)
            ->for($reviewer)
            ->create();

        $this->assertTrue($book->user->is($user));

        $this->assertTrue($book->genres->contains($genre));

        $this->assertTrue($book->reviews->contains($review));
    }
}
