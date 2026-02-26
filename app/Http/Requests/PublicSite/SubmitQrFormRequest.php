<?php

namespace App\Http\Requests\PublicSite;

use App\Models\Qr;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $token = (string) $this->input('token');
            if ($token === '') {
                return;
            }

            $qr = Qr::query()
                ->where('token', $token)
                ->where('status', 'active')
                ->with('questions')
                ->first();

            if (! $qr) {
                return;
            }

            $answers = (array) $this->input('answers', []);

            foreach ($qr->questions as $question) {
                if (! $question->is_required) {
                    continue;
                }

                $raw = $answers[(string) $question->id] ?? $answers[$question->id] ?? null;

                if (! $this->hasValue($question->type, $raw)) {
                    $label = (string) ($question->label ?? $question->question_text ?? 'This field');
                    $validator->errors()->add("answers.{$question->id}", "{$label} is required.");
                }
            }
        });
    }

    private function hasValue(string $type, mixed $value): bool
    {
        if ($type === 'checkbox') {
            return is_array($value) && count(array_filter($value, fn ($v) => $v !== null && $v !== '')) > 0;
        }

        return ! ($value === null || (is_string($value) && trim($value) === ''));
    }
}
