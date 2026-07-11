<?php

namespace App\Http\Controllers;

use App\Models\Book;

class FavoriteController extends Controller
{
    // PG10: お気に入り一覧
    public function index()
    {
        $favoriteBooks = auth()->user()->favoriteBooks()->latest()->paginate(10);

        return view('favorites.index', compact('favoriteBooks'));
    }

    // 追加と削除を自動で切り替えるトグル処理
    public function toggle(Book $book)
    {
        // ログイン中のユーザーのお気に入り書籍（favoriteBooks）に対してトグル処理を実行
        auth()->user()->favoriteBooks()->toggle($book->id);

        return back()->with('success', 'お気に入りを更新しました。');
    }
}
