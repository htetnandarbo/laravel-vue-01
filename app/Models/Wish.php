<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wish extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_downloaded' => 'boolean',
    ];

    public function qr()
    {
        return $this->belongsTo(Qr::class);
    }
}
