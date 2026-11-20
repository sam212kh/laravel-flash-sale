<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_reserve_and_convert_reservation_to_order(): void
    {
        $product = Product::query()->create([
            'name' => 'Demo',
            'sku' => 'DEMO-1',
            'stock' => 1,
        ]);

        $reserve = $this->postJson("/api/products/{$product->id}/reserve", ['user_id' => 1]);
        $reserve->assertCreated()->assertJsonPath('data.status', 'active');

        $reservationId = $reserve->json('data.id');

        $this->postJson('/api/orders', [
            'reservation_id' => $reservationId,
            'user_id' => 1,
        ])->assertCreated();

        $this->assertDatabaseHas('reservations', [
            'id' => $reservationId,
            'status' => 'converted',
        ]);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_expired_reservation_cannot_be_converted_to_order(): void
    {
        $product = Product::query()->create([
            'name' => 'Demo',
            'sku' => 'DEMO-2',
            'stock' => 1,
        ]);

        $reservation = Reservation::query()->create([
            'product_id' => $product->id,
            'user_id' => 1,
            'status' => 'active',
            'expires_at' => now()->subSecond(),
        ]);

        $this->postJson('/api/orders', [
            'reservation_id' => $reservation->id,
            'user_id' => 1,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('orders', 0);
    }
}
