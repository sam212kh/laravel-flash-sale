<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderStoreRequest;

class OrderController extends Controller
{
    public function store(OrderStoreRequest $request, InventoryService $inventory)
    {

        $order = $inventory->placeOrder((int) $request->reservation_id, (int) $request->user_id);

        return response()->json([
            'data' => [
                'id' => $order->id,
                'reservation_id' => $order->reservation_id,
                'product_id' => $order->product_id,
                'user_id' => $order->user_id,
                'created_at' => $order->created_at,
            ],
        ], 201);
    }
}
