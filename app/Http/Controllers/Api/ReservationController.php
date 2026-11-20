<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\ReservationStoreRequest;
use App\Models\Product;

class ReservationController extends Controller
{
    public function store(ReservationStoreRequest $request, Product $product, InventoryService $inventory)
    {

        $reservation = $inventory->reserve($product->id, (int) $request->user_id);

        return response()->json([
            'data' => [
                'id' => $reservation->id,
                'product_id' => $reservation->product_id,
                'user_id' => $reservation->user_id,
                'status' => $reservation->status,
                'expires_at' => $reservation->expires_at,
            ],
        ], 201);
    }
}
