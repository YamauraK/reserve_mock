<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignProductStore extends Model
{
    protected $table = 'campaign_product_store';
    protected $fillable = ['campaign_id', 'product_id', 'store_id', 'planned_quantity', 'reserved_quantity', 'is_available'];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
