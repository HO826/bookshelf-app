<?php

namespace App\Http\Requests;

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
            'title' => ['required', 'string'],
            'author' => ['required', 'string'],
            'isbn' => ['required', 'digits:13', Rule::unique('books', 'isbn')->ignore($this->book)],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
            'genres' => ['required', 'array', 'min:1'],
            'genres.*' => ['exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください',
            'author.required' => '著者を入力してください',
            'isbn.required' => '国際標準図書番号を入力してください',
            'isbn.digits' => '13桁で入力してください',
            'isbn.unique' => '国際標準図書番号は既に登録されています',
            'published_date.required' => '出版日を入力してください',
            'published_date.date' => '日付形式で入力してください',
            'image_url.url' => '画像URLは有効なURLを入力してください',
            'genres.required' => 'ジャンルを入力してください',
            'genres.array' => 'ジャンルの形式が正しくありません',
            'genres.min' => 'ジャンルは1つ以上選択してください',
            'genres.*.exists' => '選択したジャンルは存在しません',
        ];
    }
}
