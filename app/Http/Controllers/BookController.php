<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    public function index(Request $request)
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
                $query->orderBy('reviews_avg_rating', 'desc');
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

        // $books = Book::with('genres')->withAvg('reviews', 'rating')->orderBy('id')->paginate(10);

        // return view('books.index', compact('books'));
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return view('books.show', compact('book'));
    }

    public function create()
    {
        $genres = Genre::all();

        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

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

        return redirect()
            ->route('books.index', $book)
            ->with('success', '書籍を新規登録しました。');
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $book->load('genres');

        $genres = Genre::all();

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        $book->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        $book->genres()->sync($validated['genres'] ?? []);

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍情報を削除しました。');
    }

    public function fetchByIsbn($isbn)
    {
        // ハイフンやスペースを除去して数字だけに整形
        $cleanedIsbn = preg_replace('/[^0-9]/', '', $isbn);

        // 桁数チェック（13桁でない場合はエラー返却）
        if (strlen($cleanedIsbn) !== 13) {
            return response()->json([
                'error' => 'ISBNは13桁で入力してください。',
            ], 400);
        }

        // Google Books API へリクエスト送信
        $response = Http::get("https://www.googleapis.com/books/v1/volumes?q=isbn:{$cleanedIsbn}");

        // API通信失敗または該当データが存在しない場合のハンドリング
        if ($response->failed() || empty($response['items'])) {
            return response()->json([
                'error' => '該当する書籍情報が見つかりませんでした。',
            ], 404);
        }

        // 取得した書籍データの抽出
        $volumeInfo = $response['items'][0]['volumeInfo'];

        // 著者名の整形（配列形式で返ってくるためカンマ区切りの文字列にする）
        $author = isset($volumeInfo['authors'])
            ? implode(', ', $volumeInfo['authors'])
            : '';

        // 画像URLの取得（http通信の場合はhttpsに変換して補完）
        $imageUrl = $volumeInfo['imageLinks']['thumbnail'] ?? '';
        if ($imageUrl) {
            $imageUrl = str_replace('http://', 'https://', $imageUrl);
        }

        // 6. JavaScript側で受け取るプロパティ名に合わせてレスポンスを返却
        return response()->json([
            'title' => $volumeInfo['title'] ?? '',
            'author' => $author,
            'description' => $volumeInfo['description'] ?? '',
            'image_url' => $imageUrl,
            'published_date' => $volumeInfo['publishedDate'] ?? null,
        ]);
    }
}
