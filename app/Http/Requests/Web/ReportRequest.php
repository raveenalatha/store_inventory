<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'from' => $this->filled('from') ? $this->input('from') : now()->subDays(29)->toDateString(),
            'to' => $this->filled('to') ? $this->input('to') : now()->toDateString(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ];
    }
}
