<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index()
    {
        $products = Cache::remember(
            'products:index',
            now()->addSeconds(15),
            function () {
                return Product::query()
                    ->withInventoryCounts()
                    ->orderBy('id')
                    ->get()
                    ->map(fn (Product $product) => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'stock' => $product->stock,
                        'available_stock' => $product->available_stock,
                    ]);
            }
        );

        return response()->json([
            'data' => $products,
        ]);
    }
}
