<?php

namespace App\Http\Requests\PublicSite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWishCardImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'token' => (string) $this->route('token'),
            'message' => trim((string) $this->input('message', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', Rule::exists('qrs', 'token')->where('status', 'active')],
            'message' => ['required', 'string', 'max:1000'],
            'image_data' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ];
    }
}
