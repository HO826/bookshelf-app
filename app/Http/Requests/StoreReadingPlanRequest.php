<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
                Rule::unique('reading_plans', 'book_id')->where(function ($query) {
                    return $query->where('user_id', $this->user()->id);
                }),
            ],
            'target_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.integer' => '整数を入力してください',
            'book_id.exists' => '選択された書籍が存在しません',
            'book_id.unique' => 'この書籍は既に読書計画に登録されています',

            'target_date.required' => '目標日を入力してください',
            'target_date.date' => '日付形式で入力してください',
        ];
    }
}
