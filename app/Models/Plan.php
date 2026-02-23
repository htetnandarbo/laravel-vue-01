<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];
    
    public function inquiries(){
        return $this->hasMany(Inquiry::class);
    }
}
