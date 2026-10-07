<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Stock;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\Auth;
use Throwable;

class StockService
{
    public function getQuantity(int $productId, int $warehouseId): float
    {
        return (float) Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('quantity');
    }

    public function increase(
        int $productId,
        int $warehouseId,
        float $quantity,
        string $movementType,
        ?object $reference = null,
        ?string $notes = null
    ): void {
        if ($quantity <= 0) {
            throw new Exception('الكمية يجب أن تكون أكبر من صفر.');
        }

        $stock = $this->prepareStock($productId, $warehouseId);

        $stock->quantity = (float) $stock->quantity + $quantity;
        $stock->save();

        $this->createMovement(
            $productId,
            $warehouseId,
            $movementType,
            $quantity,
            $reference,
            $notes
        );
    }

    public function decrease(
        int $productId,
        int $warehouseId,
        float $quantity,
        string $movementType,
        ?object $reference = null,
        ?string $notes = null
    ): void {
        if ($quantity <= 0) {
            throw new Exception('الكمية يجب أن تكون أكبر من صفر.');
        }

        $allowNegativeStock = Setting::where('key', 'allow_negative_stock')->value('value') === '1';

        $stock = $this->prepareStock($productId, $warehouseId);

        if (!$allowNegativeStock && (float) $stock->quantity < $quantity) {
            $product = Product::find($productId);
            $productName = $product?->name ?? $productId;

            throw new Exception("الكمية غير كافية للصنف: {$productName}");
        }

        $stock->quantity = (float) $stock->quantity - $quantity;
        $stock->save();

        $this->createMovement(
            $productId,
            $warehouseId,
            $movementType,
            $quantity,
            $reference,
            $notes
        );

        try {
            app(NotificationService::class)->lowStock($productId);
        } catch (Throwable $e) {
            // لا نوقف العملية بسبب خطأ إشعار
        }
    }

    private function prepareStock(int $productId, int $warehouseId): Stock
    {
        $stock = Stock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (!$stock) {
            $stock = Stock::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'quantity' => 0,
            ]);
        }

        return $stock;
    }

    private function createMovement(
        int $productId,
        int $warehouseId,
        string $movementType,
        float $quantity,
        ?object $reference,
        ?string $notes
    ): void {
        StockMovement::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'ref_type' => $reference ? strtolower(class_basename($reference)) : null,
            'ref_id' => $reference?->id,
            'user_id' =>  Auth::id(),
            'notes' => $notes,
        ]);
    }
}