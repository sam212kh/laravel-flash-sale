<?php
namespace Tests\Feature;


use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashSaleConcurrencyTest extends  TestCase
{
    use RefreshDatabase;

    public function test_only_one_reservation_can_succeed_when_stock_is_one()
    {

        $product = Product::query()->create([
            'name'  => 'Test Product',
            'sku'   => 'Product-000-1',
            'stock' => 1,
        ]);

        $firstResponse = $this->postJson(
            "api/products/{$product->id}/reserve",
            ['user_id' => 1]
        );

        $secondResponse = $this->postJson(
            "api/products/{$product->id}/reserve",
            ['user_id' => 2]
        );

        $this->assertSame(201, $firstResponse->status());
        $this->assertSame(422, $secondResponse->status());

        $this->assertDatabaseCount('reservations', 1);

        $this->assertDatabaseHas('reservations', [
            'product_id' => $product->id,
            'user_id' => 1,
            'status' => 'active',
        ]);
    }
}
