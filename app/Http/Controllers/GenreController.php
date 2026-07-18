<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use App\Models\Genre;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    public function show(Genre $genre)
    {
        $books = $genre->books()->orderBy('id')->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function create()
    {
        return view('genres.create');
    }

    public function store(StoreGenreRequest $request)
    {

        $validated = $request->validated();

        Genre::create([
            'name' => $validated['name'],
        ]);

        return redirect()->route('genres.index')->with('success', 'ジャンルを登録しました');
    }

    /**
     * PG08: ジャンル編集画面の表示
     */
    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    /**
     * PG08: ジャンル更新処理
     */
    public function update(UpdateGenreRequest $request, Genre $genre)
    {
        $validated = $request->validated();

        $genre->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('genres.index')->with('success', 'ジャンルを更新しました');
    }

    /**
     * PG08: ジャンル削除処理
     */
    public function destroy(Genre $genre)
    {
        // データベースから削除
        $genre->delete();

        // 一覧画面にリダイレクト
        return redirect()->route('genres.index')->with('success', 'ジャンルを削除しました');
    }
}
