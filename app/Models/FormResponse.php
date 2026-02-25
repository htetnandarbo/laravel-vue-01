<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    protected $guarded = [];

    public function qr()
    {
        return $this->belongsTo(Qr::class);
    }

    public function answers()
    {
        return $this->hasMany(FormResponseAnswer::class)->with('question');
    }
}
