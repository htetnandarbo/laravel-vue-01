<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicSite\SubmitQrFormRequest;
use App\Http\Requests\PublicSite\SubmitWishRequest;
use App\Http\Requests\PublicSite\StoreWishCardImageRequest;
use App\Models\Qr;
use App\Services\PublicSubmissionService;
use App\Services\WishService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicQrController extends Controller
{
    public function __construct(
        private readonly PublicSubmissionService $submissionService,
        private readonly WishService $wishService,
    ) {
    }

    public function entry(Request $request): Response
    {
        $token = trim((string) $request->query('qr', ''));

        if ($token === '') {
            return Inertia::render('Public/QrRequired', [
                'title' => 'QR Required',
                'message' => 'You need to scan a QR code to enter this page.',
            ]);
        }

        $qr = $this->findActiveQrByToken($token);

        if (! $qr) {
            return Inertia::render('Public/QrRequired', [
                'title' => 'Invalid QR',
                'message' => 'This QR link is invalid or no longer active. Please scan a valid QR code.',
            ]);
        }

        return $this->renderQrForm($qr);
    }

    public function show(string $token): Response
    {
        $qr = $this->findActiveQrByToken($token);
        abort_if(! $qr, 404);

        return $this->renderQrForm($qr);
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

        $wish = $this->wishService->create($qr, $request->validated());

        $this->wishService->storeCardImage(
            $qr,
            $wish,
            (string) $request->validated('image'),
        );


        return redirect()->route('spin.demo', ['token' => $token])->with('success', 'Wish submitted successfully.');
    }

    private function findActiveQrByToken(string $token): ?Qr
    {
        return Qr::query()
            ->where('token', $token)
            ->where('status', 'active')
            ->with('questions')
            ->first();
    }

    private function renderQrForm(Qr $qr): Response
    {
        return Inertia::render('survey/Index', [
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
}
