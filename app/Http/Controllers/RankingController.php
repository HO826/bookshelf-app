<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index()
    {
        // 1. reviewsの関係がある前提で、件数(withCount)と平均評価(withAvg)を取得
        // 2. 平均評価が高い順（降順）でソート
        // 3. 上位10件に制限
        $rankedBooks = Book::withCount('reviews')
            ->withAvg('reviews', 'rating') // 自動的に 'reviews_avg_rating' というカラム名で取得されます
            ->orderByDesc('reviews_avg_rating') // 評価の高い順
            ->orderByDesc('reviews_count')     // 同点の場合、レビュー件数が多い方を優先（任意）
            ->take(10)                         // TOP 10
            ->get();

        return view('ranking.index', compact('rankedBooks'));
    }
}
