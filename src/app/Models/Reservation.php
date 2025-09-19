<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'store_id', 'channel', 'customer_name', 'customer_kana', 'phone', 'zip', 'address1', 'address2',
        'pickup_date', 'pickup_time_slot', 'total_amount', 'status', 'notes'
    ];


    public function items(): HasMany
    {
        return $this->hasMany(ReservationItem::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
