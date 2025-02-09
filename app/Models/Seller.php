<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'user_id',
        'store_name',
        'total_order',
        'total_amount',
        'total_canceled_order'
    ];
}
