<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrPin extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    public function qr()
    {
        return $this->belongsTo(Qr::class);
    }
}
