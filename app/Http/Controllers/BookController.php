<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $query = Book::with('genres')->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre')) {
            $genreId = $request->input('genre');
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')
                    ->orderByRaw('reviews_avg_rating IS NULL ASC')
                    ->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $books = $query->paginate(10)->appends($request->query());

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    public function show(Book $book): View
    {
        $book->load(['genres', 'reviews.user']);

        return view('books.show', compact('book'));
    }

    public function create(): View
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated) {
            $book = Book::create([
                'title' => $validated['title'],
                'author' => $validated['author'],
                'isbn' => $validated['isbn'],
                'published_date' => $validated['published_date'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'user_id' => auth()->id(),
            ]);

            $book->genres()->sync($validated['genres']);

            return $book;
        });

        return redirect()
            ->route('books.index', $book)
            ->with('success', '書籍を新規登録しました。');
    }

    public function edit(Book $book): View
    {
        $this->authorize('update', $book);

        $book->load('genres');

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book): RedirectResponse
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        DB::transaction(function () use ($book, $validated) {

            $book->update([
                'title' => $validated['title'],
                'author' => $validated['author'],
                'isbn' => $validated['isbn'],
                'published_date' => $validated['published_date'],
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
            ]);

            $book->genres()->sync($validated['genres'] ?? []);
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍情報を削除しました。');
    }

    public function fetchByIsbn($isbn): JsonResponse
    {
        $cleanedIsbn = preg_replace('/[^0-9]/', '', $isbn);

        if (strlen($cleanedIsbn) !== 13) {
            return response()->json([
                'error' => 'ISBNは13桁で入力してください。',
            ], 400);
        }

        try {
            $queryParams = [
                'q' => 'isbn:'.$cleanedIsbn,
            ];

            $apiKey = config('services.google_books.key');
            if (! empty($apiKey)) {
                $queryParams['key'] = $apiKey;
            }

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ])
                ->get('https://www.googleapis.com/books/v1/volumes', $queryParams);

            if ($response->successful() && ! empty($response->json()['items'])) {
                $volumeInfo = $response->json()['items'][0]['volumeInfo'] ?? [];

                $rawAuthor = isset($volumeInfo['authors']) && is_array($volumeInfo['authors'])
                    ? implode(', ', $volumeInfo['authors'])
                    : '';

                $imageUrl = $volumeInfo['imageLinks']['thumbnail']
                    ?? $volumeInfo['imageLinks']['smallThumbnail']
                    ?? '';

                if ($imageUrl) {
                    $imageUrl = str_replace('http://', 'https://', $imageUrl);
                }

                $publishedDate = $volumeInfo['publishedDate'] ?? null;
                if ($publishedDate) {
                    $parts = explode('-', $publishedDate);
                    $year = $parts[0] ?? '';
                    $month = isset($parts[1]) ? str_pad($parts[1], 2, '0', STR_PAD_LEFT) : '01';
                    $day = isset($parts[2]) ? str_pad($parts[2], 2, '0', STR_PAD_LEFT) : '01';
                    $publishedDate = "{$year}-{$month}-{$day}";
                }

                return response()->json([
                    'title' => $volumeInfo['title'] ?? '',
                    'author' => $this->cleanAuthorName($rawAuthor),
                    'description' => $volumeInfo['description'] ?? '',
                    'image_url' => $imageUrl,
                    'published_date' => $publishedDate,
                ]);
            }

            return response()->json([
                'error' => '該当する書籍情報が見つかりませんでした。',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'サーバー内部エラー: '.$e->getMessage(),
            ], 500);
        }
    }

    private function cleanAuthorName(string $author): string
    {
        if (empty($author)) {
            return '';
        }

        $author = preg_replace('/,?\s*\d{4}-\d{0,4}/', '', $author);

        $author = preg_replace('/[\/\x{ff0f}\s]*\[?著\]?$/u', '', $author);

        if (preg_match('/^[[\x{4E00}-\x{9FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]+,[\x{4E00}-\x{9FFF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]+$/u', $author)) {
            $author = str_replace(',', '', $author);
        } else {
            $author = str_replace(',', ' ', $author);
        }

        return trim(preg_replace('/\s+/u', ' ', $author));
    }
}
