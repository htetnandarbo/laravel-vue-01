<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormResponseAnswer extends Model
{
    protected $guarded = [];

    public function formResponse()
    {
        return $this->belongsTo(FormResponse::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
