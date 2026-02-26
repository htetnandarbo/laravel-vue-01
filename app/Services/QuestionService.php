<?php

namespace App\Services;

use App\Models\Qr;
use App\Models\Question;

class QuestionService
{
    public function create(Qr $qr, array $data): Question
    {
        return $qr->questions()->create($this->normalizePayload($data));
    }

    public function update(Question $question, array $data): Question
    {
        $question->update($this->normalizePayload($data));

        return $question->refresh();
    }

    private function normalizePayload(array $data): array
    {
        $options = $data['options'] ?? null;
        $type = $data['type'];

        if (! in_array($type, ['select', 'checkbox'], true)) {
            $options = null;
        } else {
            $options = collect((array) $options)
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all();
        }
        return [
            'label' => $data['label'],
            'question_text' => $data['label'],
            'type' => $type,
            'is_required' => (bool) ($data['is_required'] ?? false),
            'options' => $options,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
