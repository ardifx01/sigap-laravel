<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InventoryLog;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesUsers = User::where('role', 'sales')->get();
        $gudangUsers = User::where('role', 'gudang')->get();
        $products = Product::where('is_active', true)->get();
        
        $orderCounter = 1;
        
        // Generate orders for the last 30 days
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends
            if ($date->isWeekend()) {
                continue;
            }
            
            // Generate 3-8 orders per day
            $orderCount = rand(3, 8);
            
            for ($j = 0; $j < $orderCount; $j++) {
                $sales = $salesUsers->random();
                $customers = Customer::where('sales_id', $sales->id)
                                   ->where('is_active', true)
                                   ->get();
                
                if ($customers->isEmpty()) continue;
                
                $customer = $customers->random();
                
                // Random time during business hours
                $orderTime = $date->copy()
                    ->setHour(rand(9, 16))
                    ->setMinute(rand(0, 59))
                    ->setSecond(rand(0, 59));
                
                // Determine order status based on age
                $status = $this->determineOrderStatus($i);
                
                $order = Order::create([
                    'nomor_order' => 'ORD-' . $date->format('Ymd') . '-' . str_pad($orderCounter, 4, '0', STR_PAD_LEFT),
                    'sales_id' => $sales->id,
                    'customer_id' => $customer->id,
                    'status' => $status,
                    'total_amount' => 0, // Will be calculated after items
                    'catatan' => $this->getRandomOrderNote(),
                    'confirmed_at' => $status !== 'pending' ? $orderTime->copy()->addHours(rand(1, 4)) : null,
                    'confirmed_by' => $status !== 'pending' ? $gudangUsers->random()->id : null,
                    'shipped_at' => in_array($status, ['shipped', 'delivered']) ? $orderTime->copy()->addHours(rand(6, 12)) : null,
                    'delivered_at' => $status === 'delivered' ? $orderTime->copy()->addHours(rand(24, 48)) : null,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]);
                
                // Add order items
                $this->createOrderItems($order, $products, $orderTime);
                
                $orderCounter++;
            }
        }
    }
    
    private function determineOrderStatus($daysAgo): string
    {
        if ($daysAgo <= 1) {
            // Recent orders - mostly pending or confirmed
            return collect(['pending', 'confirmed', 'ready'])->random();
        } elseif ($daysAgo <= 3) {
            // 2-3 days ago - mostly in progress
            return collect(['confirmed', 'ready', 'assigned', 'shipped'])->random();
        } elseif ($daysAgo <= 7) {
            // Last week - mostly delivered or shipped
            return collect(['shipped', 'delivered'])->random();
        } else {
            // Older orders - mostly delivered
            return rand(1, 10) <= 9 ? 'delivered' : 'cancelled';
        }
    }
    
    private function createOrderItems($order, $products, $orderTime)
    {
        // Each order has 2-6 different products
        $itemCount = rand(2, 6);
        $selectedProducts = $products->random($itemCount);
        
        $totalAmount = 0;
        
        foreach ($selectedProducts as $product) {
            $quantity = rand(1, 5);
            $unitPrice = $product->harga_jual;
            $totalPrice = $quantity * $unitPrice;
            
            // Determine availability based on current stock
            $availableQty = min($quantity, $product->stok_tersedia);
            $backorderQty = max(0, $quantity - $availableQty);
            
            $itemStatus = 'pending';
            if ($order->status !== 'pending') {
                if ($backorderQty > 0) {
                    $itemStatus = $availableQty > 0 ? 'partial' : 'backorder';
                } else {
                    $itemStatus = 'available';
                }
            }
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'jumlah_pesan' => $quantity,
                'jumlah_tersedia' => $availableQty,
                'jumlah_backorder' => $backorderQty,
                'harga_satuan' => $unitPrice,
                'total_harga' => $totalPrice,
                'status' => $itemStatus,
                'created_at' => $orderTime,
                'updated_at' => $orderTime,
            ]);
            
            // Update product stock if order is confirmed or delivered
            if (in_array($order->status, ['confirmed', 'ready', 'assigned', 'shipped', 'delivered']) && $availableQty > 0) {
                $product->decrement('stok_tersedia', $availableQty);
                
                // Create inventory log
                InventoryLog::create([
                    'product_id' => $product->id,
                    'user_id' => $order->confirmed_by ?? 1,
                    'type' => 'out',
                    'quantity' => $availableQty,
                    'stock_before' => $product->stok_tersedia + $availableQty,
                    'stock_after' => $product->stok_tersedia,
                    'reference_type' => 'order',
                    'reference_id' => $order->id,
                    'notes' => "Stock keluar untuk order {$order->nomor_order}",
                    'created_at' => $orderTime->copy()->addHours(rand(1, 4)),
                ]);
            }
            
            $totalAmount += $totalPrice;
        }
        
        // Update order total
        $order->update(['total_amount' => $totalAmount]);
    }
    
    private function getRandomOrderNote(): ?string
    {
        $notes = [
            'Pesanan rutin bulanan',
            'Stok untuk event weekend',
            'Pesanan khusus untuk promosi',
            'Restock produk fast moving',
            'Pesanan tambahan karena stok menipis',
            'Persiapan stok untuk hari raya',
            'Pesanan trial produk baru',
            'Stok untuk grand opening cabang baru',
            null, // Some orders have no notes
            null,
            'Pesanan urgent, mohon diprioritaskan',
            'Koordinasi dengan tim gudang',
            'Pesanan untuk display toko',
            'Stok backup untuk weekend',
            'Pesanan sesuai target bulanan',
        ];
        
        return $notes[array_rand($notes)];
    }
}
