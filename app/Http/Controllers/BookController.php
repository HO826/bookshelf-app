<?php

namespace App\Http\Controllers;

use App\Models\Book;

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
}
