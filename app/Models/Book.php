<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'image_url',
        'author',
        'isbn',
        'published_date',
        'description',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    // 本を登録したユーザー（1対多の逆）
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 本に紐づくジャンル一覧（多対多）
    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'book_genre');
    }

    // 本に対するレビュー一覧（1対多）
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // この本をお気に入りにしているユーザー一覧（多対多）
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}
