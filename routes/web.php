<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ==========================================
// 認証必須ページ (ログインが必要)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // --- 書籍関連 ---
    // PG03: 書籍登録
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');

    // PG04: 書籍編集
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');

    // --- ジャンル関連 ---
    // PG05: ジャンル一覧
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

    // PG07: ジャンル登録
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');

    // PG06: ジャンル詳細
    Route::get('/genres/{genre}', [GenreController::class, 'show'])->name('genres.show');

    // PG08: ジャンル編集
    Route::get('/genres/{genre}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    Route::put('/genres/{genre}', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{genre}', [GenreController::class, 'destroy'])->name('genres.destroy');

    // --- レビュー・いいね関連 ---
    // レビュー投稿処理（詳細画面などから送信される用）
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // PG09: レビュー編集
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // レビューいいね機能（非同期またはリダイレクト）
    Route::post('/reviews/{review}/like', [ReviewController::class, 'toggleLike'])->name('reviews.like');
    // Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');
    // Route::delete('/reviews/{review}/like', [ReviewController::class, 'unlike'])->name('reviews.unlike');

    // --- お気に入り関連 ---
    // PG10: お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // お気に入り追加・削除の切り替え（toggleに統一）
    Route::post('/books/{book}/favorite', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});

// ==========================================
// 公開ページ (ログインなしでも閲覧可能)
// ==========================================

// PG01: 書籍一覧（トップ）
Route::get('/', [BookController::class, 'index'])->name('books.index');

// PG02: 書籍詳細
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

// PG11: ランキング
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
