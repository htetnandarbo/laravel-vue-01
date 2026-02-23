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
            'page_format' => $this->filled('page_format') ? strtoupper((string) $this->input('page_format')) : null,
            'size_mode' => $this->filled('size_mode') ? strtolower((string) $this->input('size_mode')) : null,
            'size_preset' => $this->filled('size_preset') ? strtoupper((string) $this->input('size_preset')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'base_url' => ['required', 'string', 'max:2048', 'starts_with:http://,https://'],
            'page_format' => ['nullable', Rule::in(['A4', 'LETTER'])],
            'margin_mm' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'gap_mm' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'cols' => ['nullable', 'integer', 'min:1', 'max:20'],
            'rows' => ['nullable', 'integer', 'min:1', 'max:20'],
            'size_mode' => ['nullable', Rule::in(['preset', 'custom'])],
            'size_preset' => ['nullable', Rule::in(['S', 'M', 'L', 'XL'])],
            'size_mm' => ['nullable', 'numeric', 'min:18', 'max:80'],
        ];
    }
}
