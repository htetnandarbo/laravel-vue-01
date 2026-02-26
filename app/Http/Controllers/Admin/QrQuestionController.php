<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuestionRequest;
use App\Http\Requests\Admin\UpdateQuestionRequest;
use App\Models\Qr;
use App\Models\Question;
use App\Services\QuestionService;

class QrQuestionController extends Controller
{
    public function __construct(private readonly QuestionService $questionService)
    {
    }

    public function store(StoreQuestionRequest $request, Qr $qr)
    {
        $this->questionService->create($qr, $request->validated());

        return back();
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $this->questionService->update($question, $request->validated());

        return back();
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return back();
    }
}
