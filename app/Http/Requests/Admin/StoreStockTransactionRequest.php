<?php

namespace App\Http\Requests\Admin;

use App\Models\Item;
use App\Models\Qr;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreStockTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'type' => ['required', Rule::in(['in', 'out', 'adjust'])],
            'qty' => ['required', 'numeric', 'gt:0'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Qr|null $qr */
                $qr = $this->route('qr');
                $itemId = $this->integer('item_id');

                if (! $qr || ! $itemId) {
                    return;
                }

                $item = Item::query()->find($itemId);

                if ($item && (int) $item->qr_id !== (int) $qr->id) {
                    $validator->errors()->add('item_id', 'The selected item must belong to the current QR.');
                }
            },
        ];
    }
}
