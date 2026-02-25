<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQrItemRequest;
use App\Http\Requests\Admin\UpdateQrItemRequest;
use App\Models\Item;
use App\Models\Qr;

class QrItemController extends Controller
{
    public function store(StoreQrItemRequest $request, Qr $qr)
    {
        $qr->items()->create($request->validated());

        return back();
    }

    public function update(UpdateQrItemRequest $request, Item $item)
    {
        $item->update($request->validated());

        return back();
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return back();
    }
}
