<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
  protected $fillable = [
    'user_id',
    'product_id',
    'quantity'
  ];

  public function consumer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id', 'id');
  }

  public function product(): BelongsTo
  {
    return $this->belongsTo(Product::class, 'product_id', 'id');
  }
}
