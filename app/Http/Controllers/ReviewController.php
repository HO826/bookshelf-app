<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * PG04: レビュー投稿の保存処理
     *
     * show.bladeのフォーム（route('reviews.store')）から送信された
     * 評価とコメントをデータベースに保存します。
     */
    public function store(Request $request, Book $book)
    {
        // 1. バリデーション
        $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:500'],
        ], [
            'rating.required' => '評価を選択してください。',
            'rating.between' => '評価は1〜5の間で選択してください。',
            'comment.required' => 'コメントを入力してください。',
            'comment.max' => 'コメントは500文字以内で入力してください。',
        ]);

        // 2. データベースに保存（ログインしているユーザーのIDと紐付け）
        $book->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // 3. 書籍の詳細画面にリダイレクト
        return redirect()->route('books.show', $book);
    }

    /**
     * PG04: レビュー編集画面の表示
     *
     * show.bladeの @can('update', $review) を通った後に
     * 編集用フォームを表示する画面へ遷移します。
     */
    public function edit(Review $review)
    {
        return view('reviews.edit', compact('review'));
    }

    /**
     * PG04: レビュー更新処理
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:500'],
        ], [
            'rating.required' => '評価を選択してください。',
            'rating.between' => '評価は1〜5の間で選択してください。',
            'comment.required' => 'コメントを入力してください。',
            'comment.max' => 'コメントは500文字以内で入力してください。',
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('books.show', $review->book_id);
    }

    /**
     * PG04: レビュー削除処理
     *
     * show.bladeの @can('delete', $review) 内にある
     * 削除ボタン（route('reviews.destroy')）から送信されたリクエストを処理します。
     */
    public function destroy(Review $review)
    {
        $bookId = $review->book_id;
        $review->delete();

        return redirect()->route('books.show', $bookId);
    }
}
