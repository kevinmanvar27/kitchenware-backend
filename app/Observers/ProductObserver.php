<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->checkLowStock($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Check if stock_quantity was changed
        if ($product->isDirty('stock_quantity') || $product->isDirty('low_quantity_threshold')) {
            $this->checkLowStock($product);
        }
    }

    /**
     * Check if product has low stock and send notification
     */
    private function checkLowStock(Product $product): void
    {
        try {
            if ($product->isLowStock()) {
                Log::info('Low stock detected for product', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'stock_quantity' => $product->stock_quantity,
                    'threshold' => $product->low_quantity_threshold
                ]);

                $result = $this->notificationService->sendLowStockAlert($product);
                
                Log::info('Low stock notification result', $result);
            }
        } catch (\Exception $e) {
            Log::error('Error sending low stock notification', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
