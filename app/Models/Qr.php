<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qr extends Model
{
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sort_order')->orderBy('id');
    }

    public function items()
    {
        return $this->hasMany(Item::class)->orderBy('name');
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class)->latest('id');
    }

    public function formResponses()
    {
        return $this->hasMany(FormResponse::class)->latest('id');
    }

    public function wishes()
    {
        return $this->hasMany(Wish::class)->latest('id');
    }

    public function pins()
    {
        return $this->hasMany(QrPin::class)->latest('id');
    }

    public function wishImageExports()
    {
        return $this->hasMany(WishImageExport::class)->latest('id');
    }
}
