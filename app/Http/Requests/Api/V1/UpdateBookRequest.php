<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'digits:13', Rule::unique('books', 'isbn')->ignore($this->route('book'))],
            'published_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルは必須です',
            'title.string' => 'タイトルは文字列で入力してください',
            'title.max' => 'タイトルは255文字以内で入力してください',

            'author.required' => '著者名は必須です',
            'author.string' => '著者名は文字列で入力してください',
            'author.max' => '著者名は255文字以内で入力してください',

            'isbn.digits' => 'ISBNは13桁の数字で入力してください',
            'isbn.unique' => 'このISBNは既に他の書籍で使用されています',

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
