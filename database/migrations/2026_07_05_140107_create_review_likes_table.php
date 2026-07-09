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
        Schema::create('review_likes', function (Blueprint $table) {
            $table->id();
            // 「誰が」いいねしたか
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // 「どのレビューに」いいねしたか
            $table->foreignId('review_id')->constrained()->onDelete('cascade');
            $table->timestamp('created_at')->nullable();

            // 同じ人が同じレビューに何度もいいねできないように制限をかける場合（おすすめ）
            $table->unique(['user_id', 'review_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_likes');
    }
};
