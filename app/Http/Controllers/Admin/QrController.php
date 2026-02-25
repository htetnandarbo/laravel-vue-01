<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQrRequest;
use App\Http\Requests\Admin\UpdateQrRequest;
use App\Models\Qr;
use App\Services\QrService;
use App\Services\StockService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class QrController extends Controller
{
    public function __construct(
        private readonly QrService $qrService,
        private readonly StockService $stockService,
    ) {
    }

    public function index(): Response
    {
        $qrs = Qr::query()
            ->withCount(['questions', 'items', 'formResponses', 'wishes'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('admin/Qrs/Index', [
            'qrs' => $this->paginated($qrs, fn (Qr $qr) => [
                'id' => $qr->id,
                'token' => $qr->token,
                'public_url' => rtrim((string) config('app.url'), '/').'/qr?qr='.$qr->token,
                'name' => $qr->name,
                'status' => $qr->status,
                'questions_count' => $qr->questions_count,
                'items_count' => $qr->items_count,
                'responses_count' => $qr->form_responses_count,
                'wishes_count' => $qr->wishes_count,
                'created_at' => optional($qr->created_at)->toDateTimeString(),
            ]),
        ]);
    }

    public function store(StoreQrRequest $request)
    {
        $qr = $this->qrService->create($request->validated(), $request->user());

        return redirect()->route('admin.qrs.questions', $qr);
    }

    public function update(UpdateQrRequest $request, Qr $qr)
    {
        $this->qrService->update($qr, $request->validated());

        return back();
    }

    public function destroy(Qr $qr)
    {
        $qr->delete();

        return back();
    }

    public function show(Qr $qr): Response
    {
        return $this->renderManagePage($qr, 'questions');
    }

    public function questions(Qr $qr): Response
    {
        return $this->renderManagePage($qr, 'questions');
    }

    public function items(Qr $qr): Response
    {
        return $this->renderManagePage($qr, 'items');
    }

    public function stock(Qr $qr): Response
    {
        return $this->renderManagePage($qr, 'stock');
    }

    public function responses(Qr $qr): Response
    {
        return $this->renderManagePage($qr, 'responses');
    }

    public function wishes(Qr $qr): Response
    {
        return $this->renderManagePage($qr, 'wishes');
    }

    public function pins(Qr $qr): Response
    {
        return $this->renderManagePage($qr, 'pins');
    }

    private function renderManagePage(Qr $qr, string $section): Response
    {
        $qr->load(['questions', 'items']);

        $stockTransactions = null;
        $responses = null;
        $wishes = null;
        $pins = null;

        if ($section === 'stock') {
            $stockTransactions = $qr->stockTransactions()->with('item')->paginate(20)->withQueryString();
        }

        if ($section === 'responses') {
            $responses = $qr->formResponses()->with(['answers.question'])->paginate(20)->withQueryString();
        }

        if ($section === 'wishes') {
            $wishes = $qr->wishes()->paginate(20)->withQueryString();
        }

        if ($section === 'pins') {
            $pins = $qr->pins()->paginate(50)->withQueryString();
        }

        $pageMap = [
            'questions' => 'admin/Qrs/Questions',
            'items' => 'admin/Qrs/Items',
            'stock' => 'admin/Qrs/Stock',
            'responses' => 'admin/Qrs/Responses',
            'wishes' => 'admin/Qrs/Wishes',
            'pins' => 'admin/Qrs/Pins',
        ];

        return Inertia::render($pageMap[$section] ?? $pageMap['questions'], [
            'qr' => $this->qrPayload($qr, $stockTransactions, $responses, $wishes, $pins),
            'questionTypes' => ['text', 'number', 'textarea', 'select', 'checkbox', 'date'],
        ]);
    }

    private function qrPayload(
        Qr $qr,
        ?LengthAwarePaginator $stockTransactions = null,
        ?LengthAwarePaginator $responses = null,
        ?LengthAwarePaginator $wishes = null,
        ?LengthAwarePaginator $pins = null,
    ): array
    {
        return [
            'id' => $qr->id,
            'token' => $qr->token,
            'name' => $qr->name,
            'status' => $qr->status,
            'created_at' => optional($qr->created_at)->toDateTimeString(),
            'questions' => $qr->questions->map(fn ($question) => [
                'id' => $question->id,
                'label' => $question->label ?? $question->question_text,
                'type' => $question->type,
                'is_required' => (bool) $question->is_required,
                'options' => $question->options ?? [],
                'sort_order' => (int) ($question->sort_order ?? 0),
            ])->values(),
            'items' => $qr->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'color' => $item->color,
                'balance_stock' => (float) ($item->balance_stock ?? 0),
            ])->values(),
            'stock_transactions' => $stockTransactions ? $this->paginated($stockTransactions, fn ($tx) => [
                'id' => $tx->id,
                'item_id' => $tx->item_id,
                'item_name' => $tx->item?->name,
                'type' => $tx->type,
                'quantity' => (float) $tx->quantity,
                'note' => $tx->note,
                'created_at' => optional($tx->created_at)->toDateTimeString(),
            ]) : null,
            'responses' => $responses ? $this->paginated($responses, fn ($response) => [
                'id' => $response->id,
                'status' => $response->status,
                'user_identifier' => $response->user_identifier,
                'answers_preview' => $response->answers->map(fn ($answer) => [
                    'question' => $answer->question?->label ?? $answer->question?->question_text,
                    'value' => $answer->value,
                ])->values(),
                'created_at' => optional($response->created_at)->toDateTimeString(),
            ]) : null,
            'wishes' => $wishes ? $this->paginated($wishes, fn ($wish) => [
                'id' => $wish->id,
                'message' => $wish->message,
                'status' => $wish->status,
                'created_at' => optional($wish->created_at)->toDateTimeString(),
            ]) : null,
            'pins' => $pins ? $this->paginated($pins, fn ($pin) => [
                'id' => $pin->id,
                'pin_number' => $pin->pin_number,
                'is_used' => (bool) $pin->is_used,
                'created_at' => optional($pin->created_at)->toDateTimeString(),
            ]) : null,
        ];
    }

    private function paginated(LengthAwarePaginator $paginator, callable $map): array
    {
        return [
            'data' => collect($paginator->items())->map($map)->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'links' => collect($paginator->linkCollection())->map(fn ($link) => [
                    'url' => $link['url'],
                    'label' => $link['label'],
                    'active' => (bool) $link['active'],
                ])->values()->all(),
            ],
        ];
    }
}
