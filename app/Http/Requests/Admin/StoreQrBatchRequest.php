<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQrBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'size_mode' => $this->filled('size_mode') ? strtolower((string) $this->input('size_mode')) : null,
            'size_preset' => $this->filled('size_preset') ? strtoupper((string) $this->input('size_preset')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'base_url' => ['required', 'string', 'max:2048', 'starts_with:http://,https://'],
            'size_mode' => ['nullable', Rule::in(['preset', 'custom'])],
            'size_preset' => ['nullable', Rule::in(['S', 'M', 'L'])],
            'size_mm' => ['nullable', 'numeric', 'min:18', 'max:43.5'],
        ];
    }

    public function messages(): array
    {
        return [
            'size_mm.max' => 'QR size must not be greater than 43.5mm (fixed A4 layout limit).',
            'size_mm.min' => 'QR size must be at least 18mm.',
        ];
    }
}
