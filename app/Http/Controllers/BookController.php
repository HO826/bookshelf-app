<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;

class BookController extends Controller
{
    // PG01: 書籍一覧
    public function index()
    {
        $books = Book::with('genres')->withAvg('reviews', 'rating')->orderBy('id')->paginate(10);

        // 提供されているblade（books.index）にデータを渡して表示
        return view('books.index', compact('books'));
    }

    // PG02: 書籍詳細
    public function show(Book $book)
    {
        // N+1問題を防ぐため、レビューとその投稿ユーザー（user）をまとめて読み込む
        $book->load(['genres', 'reviews.user']);

        // 提供されているblade（books.show）にデータを渡して表示
        return view('books.show', compact('book'));
    }

    // PG03: 書籍登録画面の表示
    public function create()
    {
        // フォームのジャンル選択肢で使うために全ジャンルを取得
        $genres = Genre::all();

        // 提示された登録画面のBlade（books.createなど）を指定して、ジャンルデータを渡す
        // ※Bladeのファイル名が「create.blade.php」なら 'books.create' になります
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

        // 2. ジャンル（多対多リレーション）の紐付け
        // $validated['genres'] にはジャンルIDの配列（[1, 3, 5]など）が入っています
        $book->genres()->attach($validated['genres']);

        // 3. 登録完了後、一覧画面（または詳細画面）へリダイレクト
        return redirect()
            ->route('books.index', $book)
            ->with('success', '書籍を新規登録しました。');
    }

    /**
     * PG04: 書籍編集画面の表示
     */
    public function edit(Book $book)
    {
        // 1. 認証＋作成者本人かチェック（Policyのupdateメソッドを呼び出す）
        $this->authorize('update', $book);

        $book->load('genres');

        // 2. 編集フォームのジャンル選択肢用に全ジャンルを取得
        $genres = Genre::all();

        // 3. 既存データを初期値として表示するため、bookとgenresを渡す
        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * PG05: 書籍の更新処理
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        // 1. 認証＋作成者本人かチェック
        $this->authorize('update', $book);

        // 2. バリデーション（要件に応じたルール。必要であれば別途FormRequestに切り出してください）
        $validated = $request->validated();

        // 3. 書籍とジャンル紐付けの更新
        $book->update([
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
        ]);

        // 多対多の同期処理（外されたジャンルは消え、選ばれたものだけになる）
        $book->genres()->sync($validated['genres'] ?? []);

        // 4. 更新完了後、書籍詳細画面へリダイレクト
        return redirect()
            ->route('books.index', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    public function destroy(Book $book)
    {
        // 1. 認証＋作成者本人かチェック
        $this->authorize('delete', $book);

        $book->delete();

        return redirect()->route('books.index')->with('success', '書籍情報を削除しました。');
    }
}
