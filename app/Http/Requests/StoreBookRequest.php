<?php

namespace App\Http\Requests;

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
            'title.required' => 'タイトルを入力してください',
            'title.string' => 'タイトルは文字列で入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',

            'author.required' => '著者を入力してください',
            'author.string' => '著者は文字列で入力してください',
            'author.max' => '著者は255文字以内で入力してください',

            'isbn.required' => 'isbnを入力してください',
            'isbn.digits' => '13桁で入力してください',
            'isbn.unique' => 'isbnは既に登録されています',

            'published_date.required' => '出版日を入力してください',
            'published_date.date' => '日付形式で入力してください',

            'description.string' => '説明は文字列で入力してください',

            'image_url.url' => '画像URLは有効なURLを入力してください',

            'genres.required' => 'ジャンル選択は必須です',
            'genres.array' => 'ジャンル選択が正しくありません',
            'genres.min' => 'ジャンルは1つ以上選択してください',

            'genres.*.integer' => 'ジャンルの指定形式が不正です',
            'genres.*.exists' => '選択したジャンルは存在しません',
        ];
    }
}
