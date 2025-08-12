<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Backorder;
use App\Models\OrderItem;
use Carbon\Carbon;

class BackorderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get order items that have backorder quantities
        $backorderItems = OrderItem::where('jumlah_backorder', '>', 0)
                                  ->with(['order', 'product'])
                                  ->get();
        
        foreach ($backorderItems as $item) {
            $backorderQty = $item->jumlah_backorder;
            $orderDate = $item->order->created_at;
            
            // Expected date is usually 7-21 days from order date
            $expectedDate = $orderDate->copy()->addDays(rand(7, 21));
            
            // Determine backorder status based on expected date
            $status = $this->determineBackorderStatus($expectedDate);
            $fulfilledQty = $this->calculateFulfilledQuantity($backorderQty, $status);
            
            Backorder::create([
                'order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'jumlah_backorder' => $backorderQty,
                'jumlah_terpenuhi' => $fulfilledQty,
                'status' => $status,
                'expected_date' => $expectedDate,
                'fulfilled_at' => $status === 'fulfilled' ? 
                    $expectedDate->copy()->subDays(rand(0, 3)) : 
                    ($status === 'partial' ? $expectedDate->copy()->addDays(rand(1, 5)) : null),
                'catatan' => $this->getRandomBackorderNote($status, $item->product->nama_barang),
                'created_at' => $orderDate->copy()->addHours(rand(2, 8)),
                'updated_at' => $orderDate->copy()->addHours(rand(2, 8)),
            ]);
        }
        
        // Create some additional backorders for products that are frequently out of stock
        $this->createAdditionalBackorders();
    }
    
    private function determineBackorderStatus($expectedDate): string
    {
        $now = Carbon::now();
        $daysPastExpected = $expectedDate->diffInDays($now, false);
        
        if ($daysPastExpected < 0) {
            // Future expected date - still pending
            return 'pending';
        } elseif ($daysPastExpected <= 3) {
            // Recently expected - 70% fulfilled, 20% partial, 10% pending
            $rand = rand(1, 10);
            if ($rand <= 7) return 'fulfilled';
            if ($rand <= 9) return 'partial';
            return 'pending';
        } elseif ($daysPastExpected <= 7) {
            // A week past expected - 80% fulfilled, 15% partial, 5% cancelled
            $rand = rand(1, 20);
            if ($rand <= 16) return 'fulfilled';
            if ($rand <= 19) return 'partial';
            return 'cancelled';
        } else {
            // Long overdue - 60% fulfilled, 20% partial, 20% cancelled
            $rand = rand(1, 10);
            if ($rand <= 6) return 'fulfilled';
            if ($rand <= 8) return 'partial';
            return 'cancelled';
        }
    }
    
    private function calculateFulfilledQuantity($backorderQty, $status): int
    {
        switch ($status) {
            case 'fulfilled':
                return $backorderQty;
            case 'partial':
                return rand(1, $backorderQty - 1);
            case 'pending':
            case 'cancelled':
            default:
                return 0;
        }
    }
    
    private function createAdditionalBackorders()
    {
        // Find products with low stock that might have additional backorders
        $lowStockItems = OrderItem::whereHas('product', function($query) {
                                     $query->whereColumn('stok_tersedia', '<=', 'stok_minimum');
                                 })
                                 ->where('jumlah_backorder', 0)
                                 ->where('created_at', '>=', Carbon::now()->subDays(14))
                                 ->with(['order', 'product'])
                                 ->get();
        
        foreach ($lowStockItems as $item) {
            // 30% chance of creating additional backorder due to stock shortage
            if (rand(1, 10) <= 3) {
                $additionalBackorder = rand(1, $item->jumlah_pesan);
                $orderDate = $item->order->created_at;
                $expectedDate = $orderDate->copy()->addDays(rand(10, 30));
                
                Backorder::create([
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'jumlah_backorder' => $additionalBackorder,
                    'jumlah_terpenuhi' => 0,
                    'status' => 'pending',
                    'expected_date' => $expectedDate,
                    'fulfilled_at' => null,
                    'catatan' => "Backorder tambahan karena stok {$item->product->nama_barang} menipis",
                    'created_at' => $orderDate->copy()->addDays(rand(1, 3)),
                    'updated_at' => $orderDate->copy()->addDays(rand(1, 3)),
                ]);
                
                // Update the original order item
                $item->update([
                    'jumlah_backorder' => $item->jumlah_backorder + $additionalBackorder,
                    'status' => $item->jumlah_tersedia > 0 ? 'partial' : 'backorder'
                ]);
            }
        }
    }
    
    private function getRandomBackorderNote($status, $productName): ?string
    {
        $notes = [
            'pending' => [
                "Menunggu restock {$productName} dari supplier",
                "Stok {$productName} sedang dalam perjalanan",
                "Koordinasi dengan tim procurement untuk {$productName}",
                "Estimasi {$productName} tiba minggu depan",
                "Supplier {$productName} mengalami keterlambatan",
            ],
            'partial' => [
                "Sebagian {$productName} sudah tersedia",
                "Stok {$productName} terpenuhi sebagian, sisanya menyusul",
                "Pengiriman {$productName} dilakukan bertahap",
                "Sisa {$productName} akan dikirim terpisah",
            ],
            'fulfilled' => [
                "Backorder {$productName} telah terpenuhi seluruhnya",
                "Stok {$productName} sudah lengkap dan siap kirim",
                "Pengiriman {$productName} sesuai jadwal",
                "Customer puas dengan pemenuhan {$productName}",
            ],
            'cancelled' => [
                "Backorder {$productName} dibatalkan atas permintaan customer",
                "Supplier {$productName} tidak dapat memenuhi pesanan",
                "Customer beralih ke produk alternatif",
                "Backorder {$productName} expired dan dibatalkan",
            ],
        ];
        
        $statusNotes = $notes[$status] ?? [''];
        return $statusNotes[array_rand($statusNotes)] ?: null;
    }
}
