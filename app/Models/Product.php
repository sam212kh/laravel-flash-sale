<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $fillable = [
        'name', 'sku', 'stock'
    ];


    protected function casts(): array
    {
        return [
            'stock' => 'integer',
        ];
    }


    public function scopeWithInventoryCounts(Builder $query): Builder
    {
        return $query->withCount([
            'reservations as active_reservations_count' => fn ($query) =>
            $query->where('status', 'active')
                ->where('expires_at', '>', now()),

            'orders as orders_count',
        ]);
    }

    public function reservations() : HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function orders() : HasMany
    {
        return $this->hasMany(Order::class);
    }


    protected function availableStock(): Attribute
    {
        return Attribute::get(
            fn () => max(
                0,
                $this->stock
                - $this->active_reservations_count
                - $this->orders_count
            )
        );
    }
}
