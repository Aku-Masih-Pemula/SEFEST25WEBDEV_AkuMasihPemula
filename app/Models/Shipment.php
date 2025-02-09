<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'tracking_number',
        'status',
        'delivered_at',
    ];

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', ownerKey: 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', ownerKey: 'id');
    }
}
