<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'digits:13', 'unique:books,isbn'],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ユーザーIDは必須です',
            'user_id.integer' => 'ユーザーIDは整数で指定してください',
            'user_id.exists' => '指定されたユーザーが存在しません',

            'title.required' => 'タイトルは必須です',
            'title.string' => 'タイトルは文字列で入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',

            'author.required' => '著者名は必須です',
            'author.string' => '著者名は文字列で入力してください',
            'author.max' => '著者名は255文字以内で入力してください',

            'isbn.required' => 'ISBNは必須です',
            'isbn.digits' => 'ISBNは13桁の数字で入力してください',
            'isbn.unique' => 'このISBNは既に登録されています',

            'published_date.required' => '出版日は必須です',
            'published_date.date' => '出版日は正しい日付形式で入力してください',

            'description.string' => '説明は文字列で入力してください',

            'image_url.url' => '画像URLは正しいURL形式で入力してください',

            'genres.required' => 'ジャンル選択は必須です',
            'genres.array' => 'ジャンルの指定形式が不正です',
            'genres.min' => 'ジャンルは最低1つ選択してください',

            'genres.*.integer' => 'ジャンルは整数で指定してください',
            'genres.*.exists' => '選択されたジャンルが存在しません',
        ];
    }
}
