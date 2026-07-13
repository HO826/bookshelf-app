<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * PG05: ジャンル一覧画面の表示
     */
    public function index()
    {
        // 1. データベースからすべてのジャンルデータを取得
        $genres = Genre::withCount('books')->get();

        // 2. 取得したデータを一覧表示用のBlade（画面）に渡す
        return view('genres.index', compact('genres'));
    }

    /**
     * PG06: ジャンル詳細画面の表示
     */
    public function show(Genre $genre)
    {
        // このジャンルに登録されている書籍を、紐づくユーザー情報と一緒に取得（ページネーション付き）
        $books = $genre->books()->with('user')->orderBy('id')->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    /**
     * PG07: ジャンル登録画面の表示
     */
    public function create()
    {
        return view('genres.create');
    }

    /**
     * PG07: ジャンル登録の保存処理
     */
    public function store(Request $request)
    {
        // 1. バリデーション（入力チェック）
        $request->validate([
            'name' => ['required', 'string', 'max:20', 'unique:genres,name'],
        ], [
            'name.required' => 'ジャンル名は必須です。',
            'name.max' => 'ジャンル名は20文字以内で入力してください。',
            'name.unique' => 'そのジャンル名は既に登録されています。',
        ]);

        // 2. データベースに保存
        Genre::create([
            'name' => $request->name,
        ]);

        // 3. 一覧画面にリダイレクトし、成功メッセージを送る
        return redirect()->route('genres.index');
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
    public function update(Request $request, Genre $genre)
    {
        // 1. バリデーション（自分自身の名前は重複エラーから除外する）
        $request->validate([
            'name' => ['required', 'string', 'max:20', 'unique:genres,name,'.$genre->id],
        ], [
            'name.required' => 'ジャンル名は必須です。',
            'name.max' => 'ジャンル名は20文字以内で入力してください。',
            'name.unique' => 'そのジャンル名は既に登録されています。',
        ]);

        // 2. データベースの更新
        $genre->update([
            'name' => $request->name,
        ]);

        // 3. 一覧画面にリダイレクト
        return redirect()->route('genres.index');
    }

    /**
     * PG08: ジャンル削除処理
     */
    public function destroy(Genre $genre)
    {
        // データベースから削除
        $genre->delete();

        // 一覧画面にリダイレクト
        return redirect()->route('genres.index');
    }
}
