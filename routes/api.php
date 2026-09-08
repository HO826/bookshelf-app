<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login'])->name('api.login');

    Route::get('/books', [BookController::class, 'index'])->name('api.books.index');

    Route::get('/books/{book}', [BookController::class, 'show'])->name('api.books.show');

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

        Route::post('/books', [BookController::class, 'store'])->name('api.books.store');

        Route::put('/books/{book}', [BookController::class, 'update'])->name('api.books.update');

        Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('api.books.destroy');
    });
});
