<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_date.required' => '目標日を入力してください',
            'target_date.date' => '日付形式で入力してください',
            'target_date.after_or_equal' => '過去の日付に遡っての目標日設定は出来ません',
        ];
    }
}
