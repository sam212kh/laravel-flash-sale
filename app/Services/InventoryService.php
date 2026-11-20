<?php
namespace App\Services;


use App\Jobs\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class InventoryService
{
    private const PRODUCT_CACHE_KEY = 'products:index';

    public function placeOrder(int $reservationId, int $userId): Order
    {
        $order = DB::transaction(function () use ($reservationId, $userId) {
            $reservationSnapshot = Reservation::query()->findOrFail($reservationId);
            $product = Product::query()->lockForUpdate()->findOrFail($reservationSnapshot->product_id);
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservationId);

            if ((int) $reservation->user_id !== $userId) {
                throw ValidationException::withMessages([
                    'reservation_id' => ['This reservation does not belong to the user.'],
                ]);
            }

            if ($reservation->status !== 'active' || $reservation->expires_at->isPast()) {
                if ($reservation->status === 'active') {
                    $reservation->update(['status' => 'expired']);
                    DB::afterCommit(fn () => Cache::forget(self::PRODUCT_CACHE_KEY));
                }

                throw ValidationException::withMessages([
                    'reservation_id' => ['Reservation has expired or is no longer valid.'],
                ]);
            }

            if ($reservation->order()->exists()) {
                throw ValidationException::withMessages([
                    'reservation_id' => ['This reservation has already been converted.'],
                ]);
            }

            $reservation->update(['status' => 'converted']);

            $order = Order::query()->create([
                'reservation_id' => $reservation->id,
                'product_id' => $product->id,
                'user_id' => $userId,
            ]);

            DB::afterCommit(function () use ($order) {
                Cache::forget(self::PRODUCT_CACHE_KEY);
                OrderPlaced::dispatch($order->id);
            });

            return $order;
        }, attempts: 3);

        return $order;
    }



    public function reserve(int $productId, int $userId): Reservation
    {
        return DB::transaction(function () use ($productId, $userId) {
            // Every inventory-changing operation locks the product row first.
            $product = Product::query()->lockForUpdate()->findOrFail($productId);

            $this->expireReservationsForProduct($product);

            $active = $product->reservations()
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->count();

            $sold = $product->orders()->count();

            if (($product->stock - $active - $sold) < 1) {
                throw ValidationException::withMessages([
                    'product' => ['Insufficient available stock.'],
                ]);
            }

            $reservation = $product->reservations()->create([
                'user_id' => $userId,
                'status' => 'active',
                'expires_at' => now()->addMinutes(5),
            ]);

            DB::afterCommit(fn () => Cache::forget(self::PRODUCT_CACHE_KEY));

            return $reservation;
        }, attempts: 3);
    }



    public function expireReservations(): int
    {
        $expiredCount = 0;

        Product::query()->select('id')->orderBy('id')->chunkById(100, function ($products) use (&$expiredCount) {
            foreach ($products as $item) {
                $count = DB::transaction(function () use ($item) {
                    $product = Product::query()->lockForUpdate()->find($item->id);
                    if (! $product) {
                        return 0;
                    }

                    return $this->expireReservationsForProduct($product);
                }, attempts: 3);

                $expiredCount += $count;
            }
        });

        if ($expiredCount > 0) {
            Cache::forget(self::PRODUCT_CACHE_KEY);
        }

        return $expiredCount;
    }

    private function expireReservationsForProduct(Product $product): int
    {
        return $product->reservations()
            ->where('status', 'active')
            ->where('expires_at', '<=', now())
            ->update(['status' => 'expired', 'updated_at' => now()]);
    }
}
