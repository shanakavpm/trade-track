<?php

namespace App\Services\Stock;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    public function reserve(int $productId, int $quantity): bool
    {
        return DB::transaction(function () use ($productId, $quantity) {
            $product = Product::where('id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                Log::error('Product not found for stock reservation', [
                    'product_id' => $productId,
                ]);
                return false;
            }

            if ($product->stock_quantity < $quantity) {
                Log::warning('Insufficient stock', [
                    'product_id' => $productId,
                    'requested' => $quantity,
                    'available' => $product->stock_quantity,
                ]);
                return false;
            }

            $product->stock_quantity -= $quantity;
            $product->save();

            Log::info('Stock reserved', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'remaining' => $product->stock_quantity,
            ]);

            return true;
        });
    }

    public function restore(int $productId, int $quantity): void
    {
        DB::transaction(function () use ($productId, $quantity) {
            $product = Product::where('id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                Log::error('Product not found for stock restoration', [
                    'product_id' => $productId,
                ]);
                return;
            }

            $product->stock_quantity += $quantity;
            $product->save();

            Log::info('Stock restored', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'new_total' => $product->stock_quantity,
            ]);
        });
    }
}
