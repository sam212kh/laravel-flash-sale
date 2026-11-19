<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'reservation_id', 'status',
    ];


    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
        ];
    }
    public function reservation() : BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }


    public function product() : BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
