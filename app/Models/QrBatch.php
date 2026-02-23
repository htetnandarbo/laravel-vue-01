<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QrBatch extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'margin_mm' => 'float',
            'gap_mm' => 'float',
            'size_mm' => 'float',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(QrBatchItem::class);
    }
}
