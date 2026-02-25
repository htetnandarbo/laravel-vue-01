<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('balance_stock')) {
            $this->merge([
                'balance_stock' => $this->input('balance_stock') === '' ? null : $this->input('balance_stock'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'balance_stock' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
