<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Qr;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function currentStock(Item $item): float
    {
        return (float) ($item->balance_stock ?? 0);
    }

    public function createTransaction(Qr $qr, array $data, ?User $user): StockTransaction
    {
        /** @var Item $item */
        $item = Item::query()->findOrFail($data['item_id']);

        if ((int) $item->qr_id !== (int) $qr->id) {
            throw ValidationException::withMessages([
                'item_id' => ['The selected item must belong to the current QR.'],
            ]);
        }

        $qty = (float) $data['qty'];
        $type = $data['type'];

        if ($type === 'out') {
            $current = $this->currentStock($item);

            if ($qty > $current) {
                throw ValidationException::withMessages([
                    'qty' => ['Insufficient stock for this item.'],
                ]);
            }
        }

        return DB::transaction(function () use ($qr, $item, $data, $user, $qty) {
            $transaction = StockTransaction::query()->create([
                'qr_id' => $qr->id,
                'item_id' => $item->id,
                'type' => $data['type'],
                'quantity' => $qty,
                'note' => $data['note'] ?? null,
                'created_by' => $user?->id,
            ]);

            $delta = match ($data['type']) {
                'in', 'adjust' => $qty,
                'out' => -$qty,
                default => 0,
            };

            $item->forceFill([
                'balance_stock' => (float) ($item->balance_stock ?? 0) + $delta,
            ])->save();

            return $transaction;
        });
    }
}
