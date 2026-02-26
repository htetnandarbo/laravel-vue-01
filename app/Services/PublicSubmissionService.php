<?php

namespace App\Services;

use App\Models\FormResponse;
use App\Models\FormResponseAnswer;
use App\Models\Qr;
use App\Models\Question;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicSubmissionService
{
    public function submit(Qr $qr, array $payload): FormResponse
    {
        $questions = $qr->questions()->get();
        $answers = (array) ($payload['answers'] ?? []);

        $normalizedAnswers = $this->validateAgainstSchema($questions, $answers);

        return DB::transaction(function () use ($qr, $payload, $normalizedAnswers) {
            $response = FormResponse::query()->create([
                'qr_id' => $qr->id,
                'user_identifier' => $payload['user_identifier'] ?? null,
                'status' => 'new',
            ]);

            foreach ($normalizedAnswers as $questionId => $value) {
                FormResponseAnswer::query()->create([
                    'form_response_id' => $response->id,
                    'question_id' => $questionId,
                    'value' => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value,
                ]);
            }

            return $response->load('answers.question');
        });
    }

    private function validateAgainstSchema($questions, array $answers): array
    {
        $errors = [];
        $normalized = [];

        /** @var Question $question */
        foreach ($questions as $question) {
            $key = (string) $question->id;
            $exists = array_key_exists($key, $answers) || array_key_exists($question->id, $answers);
            $raw = $answers[$key] ?? $answers[$question->id] ?? null;

            if ($question->is_required && ! $this->hasValue($question->type, $raw)) {
                $errors["answers.{$question->id}"] = ["{$question->label} is required."];
                continue;
            }

            if (! $this->hasValue($question->type, $raw)) {
                continue;
            }

            $result = $this->normalizeAnswerValue($question, $raw);

            if ($result['error']) {
                $errors["answers.{$question->id}"] = [$result['error']];
                continue;
            }

            if ($exists) {
                $normalized[$question->id] = $result['value'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $normalized;
    }

    private function hasValue(string $type, mixed $value): bool
    {
        if ($type === 'checkbox') {
            return is_array($value) && count(array_filter($value, fn ($v) => $v !== null && $v !== '')) > 0;
        }

        return ! ($value === null || (is_string($value) && trim($value) === ''));
    }

    private function normalizeAnswerValue(Question $question, mixed $value): array
    {
        $type = $question->type;
        $options = collect((array) ($question->options ?? []))->map(fn ($v) => (string) $v)->all();

        return match ($type) {
            'number' => is_numeric($value)
                ? ['value' => (string) $value, 'error' => null]
                : ['value' => null, 'error' => 'Must be a number.'],
            'date' => $this->normalizeDate($value),
            'select' => in_array((string) $value, $options, true)
                ? ['value' => (string) $value, 'error' => null]
                : ['value' => null, 'error' => 'Invalid option selected.'],
            'checkbox' => $this->normalizeCheckbox($value, $options),
            'text', 'textarea' => ['value' => trim((string) $value), 'error' => null],
            default => ['value' => null, 'error' => 'Unsupported question type.'],
        };
    }

    private function normalizeDate(mixed $value): array
    {
        try {
            return ['value' => Carbon::parse((string) $value)->toDateString(), 'error' => null];
        } catch (\Throwable) {
            return ['value' => null, 'error' => 'Invalid date value.'];
        }
    }

    private function normalizeCheckbox(mixed $value, array $options): array
    {
        if (! is_array($value)) {
            return ['value' => null, 'error' => 'Must be an array of options.'];
        }

        $clean = collect($value)->map(fn ($v) => (string) $v)->filter()->values()->all();

        foreach ($clean as $selected) {
            if (! in_array($selected, $options, true)) {
                return ['value' => null, 'error' => 'One or more selected options are invalid.'];
            }
        }

        return ['value' => $clean, 'error' => null];
    }
}
