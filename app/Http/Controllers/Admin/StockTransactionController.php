<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockTransactionRequest;
use App\Models\Qr;
use App\Services\StockService;

class StockTransactionController extends Controller
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function store(StoreStockTransactionRequest $request, Qr $qr)
    {
        $this->stockService->createTransaction($qr, $request->validated(), $request->user());

        return back();
    }
}
