<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Product::query()->updateOrCreate(
            ['sku' => 'FLASH-001'],
            ['name' => 'Flash Sale Demo Product', 'stock' => 10],
        );
    }
}
