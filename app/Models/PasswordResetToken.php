<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    // 使うテーブル名を指定
    protected $table = 'password_reset_tokens';

    // 主キーが id ではなく email
    protected $primaryKey = 'email';

    // 主キーが勝手に1から順番に自動で数字が増えるidではなく、文字列を指定
    public $incrementing = false;

    protected $keyType = 'string';

    // 一度created_atとupdated_atをオフ（書き込まない）
    public $timestamps = false;

    // created_at のみを自動管理させたい場合は以下を定義します
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];
}
