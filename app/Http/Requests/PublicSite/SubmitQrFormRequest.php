<?php

namespace App\Http\Requests\PublicSite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitQrFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'token' => (string) $this->route('token'),
        ]);
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', Rule::exists('qrs', 'token')->where('status', 'active')],
            'user_identifier' => ['nullable', 'string', 'max:255'],
            'answers' => ['required', 'array'],
        ];
    }
}
