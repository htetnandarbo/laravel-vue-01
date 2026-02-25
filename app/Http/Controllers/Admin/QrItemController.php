<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQrItemRequest;
use App\Http\Requests\Admin\UpdateQrItemRequest;
use App\Models\Item;
use App\Models\Qr;
use Illuminate\Database\QueryException;

class QrItemController extends Controller
{
    private const COLOR_PALETTE = [
        '#f59e0b', '#fcd34d', '#fb7185', '#c084fc', '#60a5fa',
        '#34d399', '#fdba74', '#f9a8d4', '#93c5fd', '#a7f3d0',
    ];

    public function store(StoreQrItemRequest $request, Qr $qr)
    {
        $qr->items()->create(array_merge($request->validated(), [
            'color' => $this->generateMutedColorForQr($qr->id),
        ]));

        return back();
    }

    public function update(UpdateQrItemRequest $request, Item $item)
    {
        $payload = $request->validated();

        if (empty($item->color)) {
            $payload['color'] = $this->generateMutedColorForQr((int) $item->qr_id, (int) $item->id);
        }

        $item->update($payload);

        return back();
    }

    public function destroy(int $item)
    {
        $model = Item::query()->find($item);

        if (! $model) {
            return back();
        }

        try {
            $model->delete();
        } catch (QueryException $e) {
            return back()->withErrors([
                'items' => 'Item cannot be deleted because it is used by related records.',
            ]);
        }

        return back();
    }

    private function generateMutedColorForQr(int $qrId, ?int $excludeItemId = null): string
    {
        $recentColors = Item::query()
            ->where('qr_id', $qrId)
            ->when($excludeItemId, fn ($q) => $q->where('id', '!=', $excludeItemId))
            ->whereNotNull('color')
            ->latest('id')
            ->limit(5)
            ->pluck('color')
            ->filter()
            ->values()
            ->all();

        $available = array_values(array_filter(
            self::COLOR_PALETTE,
            fn (string $color) => ! in_array($color, $recentColors, true)
        ));

        if ($available !== []) {
            return $available[array_rand($available)];
        }

        // If palette is exhausted, repeat with spacing based on item count to avoid immediate repetition.
        $count = (int) Item::query()->where('qr_id', $qrId)->count();
        $index = ($count * 3) % count(self::COLOR_PALETTE);

        return self::COLOR_PALETTE[$index];
    }
}
