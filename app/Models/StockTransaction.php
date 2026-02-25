<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function qr()
    {
        return $this->belongsTo(Qr::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
