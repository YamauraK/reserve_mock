<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['sku', 'name', 'description', 'price', 'manufacturer', 'is_active', 'is_all_store'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_all_store' => 'boolean',
    ];

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'product_store', 'product_id', 'store_id')
            ->withTimestamps();
    }

    public function scopeAvailableForStore($query, int $storeId)
    {
        return $query->where(function ($q) use ($storeId) {
            $q->where('is_all_store', true)
                ->orWhereHas('stores', fn($s) => $s->where('stores.id', $storeId));
        });
    }
}
