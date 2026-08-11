<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログイン画面を表示できる(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    /**
     * 正しいユーザー情報でログインできること
     */
    public function test_正しいユーザー情報でログインできる(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // ログイン状態になっていることを検証
        $this->assertAuthenticatedAs($user);

        // リダイレクト先を確認（トップページや /books など環境に合わせて変更してください）
        $response->assertRedirect('/');
    }

    /**
     * パスワードが間違っている場合はログインできないこと
     */
    public function test_誤ったパスワードではログインできない(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // ゲスト状態（未ログイン）のままであることを検証
        $this->assertGuest();

        // セッションにエラーが含まれていることを検証
        $response->assertSessionHasErrors();
    }

    /**
     * ログアウトができること
     */
    public function test_ユーザーはログアウトできる(): void
    {
        $user = User::factory()->create();

        // ログイン状態から POST リクエストでログアウト処理を呼び出す
        $response = $this->actingAs($user)
            ->post(route('logout'));

        // ゲスト状態に戻っていることを検証
        $this->assertGuest();

        // リダイレクト先を確認
        $response->assertRedirect('/');
    }
}
