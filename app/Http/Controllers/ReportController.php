<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Review;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $userReviews = Review::where('user_id', $userId);

        $summary = [
            'total_reviews' => (clone $userReviews)->count(),
            'books_read' => (clone $userReviews)->distinct('book_id')->count('book_id'),
            'average_rating' => (clone $userReviews)->avg('rating') ?? 0,
        ];

        $ratingDistribution = collect([
            0 => (clone $userReviews)->where('rating', 1)->count(),
            1 => (clone $userReviews)->where('rating', 2)->count(),
            2 => (clone $userReviews)->where('rating', 3)->count(),
            3 => (clone $userReviews)->where('rating', 4)->count(),
            4 => (clone $userReviews)->where('rating', 5)->count(),
        ]);

        $topRatedBooks = Review::with('book')
            ->where('user_id', $userId)
            ->where('rating', '>=', 4)
            ->orderBy('rating', 'desc')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => $review->rating,
                ];
            })
            ->toArray();

        $genreRatings = Genre::whereHas('books.reviews', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->get()
            ->map(function ($genre) use ($userId) {
                $userReviewsForGenre = Review::where('user_id', $userId)
                    ->whereHas('book.genres', function ($q) use ($genre) {
                        $q->where('genres.id', $genre->id);
                    });

                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'count' => (clone $userReviewsForGenre)->count(),
                    'average_rating' => (clone $userReviewsForGenre)->avg('rating') ?? 0,
                ];
            })
            ->sortByDesc('average_rating')
            ->take(5)
            ->values()
            ->toArray();

        $stats = [
            'summary' => $summary,
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}
