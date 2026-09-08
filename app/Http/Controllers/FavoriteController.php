<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $books = auth()->user()->favoriteBooks()->orderBy('id')->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book): RedirectResponse
    {
        DB::transaction(function () use ($book) {
            auth()->user()->favoriteBooks()->toggle($book->id);
        });

        return back()->with('success', 'お気に入りを更新しました。');
    }
}
