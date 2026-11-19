<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Reservation extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'status', 'expires_at'
    ];


    public function casts() : array
    {
        return ['expires_at' => 'immutable_datetime'];
    }


    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order() : HasOne
    {
        return $this->hasOne(Order::class);
    }


    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ! $this->isExpired();
    }
}
