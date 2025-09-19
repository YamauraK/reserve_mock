<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'start_date', 'end_date', 'is_active'];


    public function products()
    {
        return $this->belongsToMany(Product::class, 'campaign_product_store')
            ->withPivot(['store_id', 'planned_quantity', 'reserved_quantity', 'is_available'])
            ->withTimestamps();
    }

    public function productStores(): HasMany
    {
        return $this->hasMany(CampaignProductStore::class);
    }
}
