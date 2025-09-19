<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'address', 'phone', 'open_time', 'close_time', 'is_active'];


    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
