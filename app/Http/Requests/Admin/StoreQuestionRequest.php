<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => $this->filled('type') ? strtolower((string) $this->input('type')) : null,
            'is_required' => $this->boolean('is_required'),
        ]);
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['text', 'number', 'textarea', 'select', 'checkbox', 'date'])],
            'options' => ['nullable', 'array', Rule::requiredIf(fn () => in_array($this->input('type'), ['select', 'checkbox'], true))],
            'options.*' => ['nullable', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
