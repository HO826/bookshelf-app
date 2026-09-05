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
        Schema::create('reading_plans', function (Blueprint $table) {
            // 主キー
            $table->id();

            // 計画者 (user_id) と 対象書籍 (book_id)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();

            // 期日
            $table->date('target_date');

            // 計画状態
            $table->string('status')->default('planned');

            $table->timestamp('completed_at')->nullable();

            // 関連する日時情報 (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_plans');
    }
};
