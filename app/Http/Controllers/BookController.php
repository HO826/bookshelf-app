<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // PG01: 書籍一覧
    public function index()
    {
        // 要件：10件ずつのページネーション + N+1論外のための Eager Loading
        $books = Book::with('genres')->orderBy('id')->paginate(10);

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

    /**
     * PG04: 書籍編集画面の表示
     */
    public function edit(Book $book)
    {
        // 1. 認証＋作成者本人かチェック（Policyのupdateメソッドを呼び出す）
        $this->authorize('update', $book);

        // dd($book->published_date);

        // 2. 編集フォームのジャンル選択肢用に全ジャンルを取得
        $genres = Genre::all();

        // 3. 既存データを初期値として表示するため、bookとgenresを渡す
        return view('books.edit', compact('book', 'genres'));
    }

    /**
     * PG05: 書籍の更新処理
     */
    public function update(Request $request, Book $book)
    {
        // 1. 認証＋作成者本人かチェック
        $this->authorize('update', $book);

        // 2. バリデーション（要件に応じたルール。必要であれば別途FormRequestに切り出してください）
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'genre_id' => 'required|exists:genres,id',
            'description' => 'nullable|string|max:1000',
        ]);

        // 3. 書籍とジャンル紐付けの更新
        $book->update($validated);

        // 4. 更新完了後、書籍詳細画面へリダイレクト
        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }
}
