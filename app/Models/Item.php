<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $guarded = [];

    protected $casts = [
        'balance_stock' => 'decimal:4',
        'color' => 'string',
    ];

    public function qr()
    {
        return $this->belongsTo(Qr::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class)->latest('id');
    }

}
