<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexBookRequest;
use App\Http\Requests\Api\V1\StoreBookRequest;
use App\Http\Requests\Api\V1\UpdateBookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(IndexBookRequest $request): AnonymousResourceCollection
    {
        $query = Book::with('genres')->withAvg('reviews', 'rating')->withCount('reviews');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $genreId = $request->input('genre_id');
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        $perPage = (int) $request->input('per_page', 20);

        $books = $query->orderBy('id')->paginate($perPage);

        return BookResource::collection($books);
    }

    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = DB::transaction(function () use ($request) {
            $book = Book::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
            ]);

            $book->genres()->attach($request->genres);

            return $book;
        });

        $book->load('genres');

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Book $book): BookResource
    {
        $book->load([
            'genres',
            'reviews.user',
        ]);

        return new BookResource($book);
    }

    public function update(UpdateBookRequest $request, Book $book): BookResource
    {
        $this->authorize('update', $book);

        DB::transaction(function () use ($request, $book) {
            $book->update([
                'title' => $request->title,
                'author' => $request->author,
                'isbn' => $request->isbn,
                'published_date' => $request->published_date,
                'description' => $request->description,
                'image_url' => $request->image_url,
            ]);

            $book->genres()->sync($request->genres);
        });

        $book->load('genres');

        return new BookResource($book);
    }

    public function destroy(Book $book): JsonResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->json(null, 204);
    }
}
