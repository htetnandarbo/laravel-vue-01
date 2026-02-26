<?php

namespace App\Http\Requests\PublicSite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitWishRequest extends FormRequest
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
            'message' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }
}
