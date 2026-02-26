<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWishStatusRequest;
use App\Models\Qr;
use App\Models\Wish;
use App\Services\WishService;

class WishController extends Controller
{
    public function __construct(private readonly WishService $wishService)
    {
    }

    public function index(Qr $qr)
    {
        return redirect()->route('admin.qrs.wishes', ['qr' => $qr->id]);
    }

    public function update(UpdateWishStatusRequest $request, Wish $wish)
    {
        $this->wishService->updateStatus($wish, $request->validated('status'));

        return back();
    }
}
