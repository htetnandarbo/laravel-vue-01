<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrBatchItem extends Model
{
    protected $guarded = [];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(QrBatch::class, 'qr_batch_id');
    }
}
