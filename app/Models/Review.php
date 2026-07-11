<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
    ];

    // レビューを書いたユーザー（1対多の逆）
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // レビュー対象の本（1対多の逆）
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // このレビューにいいねしたユーザー一覧（多対多）
    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'review_likes');
    }
}
