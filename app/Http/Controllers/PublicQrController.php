<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicSite\SubmitQrFormRequest;
use App\Http\Requests\PublicSite\SubmitWishRequest;
use App\Models\Qr;
use App\Services\PublicSubmissionService;
use App\Services\WishService;
use Inertia\Inertia;
use Inertia\Response;

class PublicQrController extends Controller
{
    public function __construct(
        private readonly PublicSubmissionService $submissionService,
        private readonly WishService $wishService,
    ) {
    }

    public function show(string $token): Response
    {
        $qr = Qr::query()
            ->where('token', $token)
            ->where('status', 'active')
            ->with('questions')
            ->firstOrFail();

        return Inertia::render('Public/QrForm', [
            'qr' => [
                'id' => $qr->id,
                'token' => $qr->token,
                'name' => $qr->name,
                'status' => $qr->status,
            ],
            'questions' => $qr->questions->map(fn ($question) => [
                'id' => $question->id,
                'label' => $question->label ?? $question->question_text,
                'type' => $question->type,
                'is_required' => (bool) $question->is_required,
                'options' => $question->options ?? [],
                'sort_order' => (int) ($question->sort_order ?? 0),
            ])->values(),
        ]);
    }

    public function submit(SubmitQrFormRequest $request, string $token)
    {
        $qr = Qr::query()->where('token', $token)->where('status', 'active')->firstOrFail();

        $this->submissionService->submit($qr, $request->validated());

        return back()->with('success', 'Form submitted successfully.');
    }

    public function wish(SubmitWishRequest $request, string $token)
    {
        $qr = Qr::query()->where('token', $token)->where('status', 'active')->firstOrFail();

        $this->wishService->create($qr, $request->validated());

        return back()->with('success', 'Wish submitted successfully.');
    }
}
