<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            // 主キー
            $table->id();

            // 通知種別(システム通知、いいね通知)
            $table->string('type');

            // 受信者(ユーザーやチーム)
            // notifiable_type + notifiable_id + index
            $table->morphs('notifiable');

            // 通知内容
            $table->text('data');

            // 既読状態 nullなら未読
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
