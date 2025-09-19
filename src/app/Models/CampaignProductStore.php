<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignProductStore extends Model
{
    protected $table = 'campaign_product_store';
    protected $fillable = ['campaign_id', 'product_id', 'store_id', 'planned_quantity', 'reserved_quantity', 'is_available'];
}
