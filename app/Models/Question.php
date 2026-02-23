<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $guarded = [];

    public function questionOptions()
    {
        return $this->hasMany(QuestionOption::class)
            ->orderBy('order');
    }
}
