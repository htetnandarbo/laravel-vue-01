<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishImageExport extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function qr()
    {
        return $this->belongsTo(Qr::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
