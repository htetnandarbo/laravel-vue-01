<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];
    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'array',
    ];

    public function qr()
    {
        return $this->belongsTo(Qr::class);
    }

    public function questionOptions()
    {
        return $this->hasMany(QuestionOption::class)
            ->orderBy('order');
    }

    public function responseAnswers()
    {
        return $this->hasMany(FormResponseAnswer::class);
    }
}
