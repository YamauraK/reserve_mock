<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyBirdScope extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'early_bird_discount_id','product_id','store_id'
    ];

    public function discount()
    {
        return $this->belongsTo(EarlyBirdDiscount::class,'early_bird_discount_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
