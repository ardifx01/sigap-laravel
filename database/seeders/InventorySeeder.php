<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gudangUsers = User::where('role', 'gudang')->get();
        $products = Product::where('is_active', true)->get();
        
        // Create initial stock entries (stock in)
        $this->createInitialStockEntries($products, $gudangUsers);
        
        // Create regular stock movements
        $this->createRegularStockMovements($products, $gudangUsers);
        
        // Create stock adjustments
        $this->createStockAdjustments($products, $gudangUsers);
    }
    
    private function createInitialStockEntries($products, $gudangUsers)
    {
        foreach ($products as $product) {
            // Create initial stock entry (stock in) for each product
            $initialDate = Carbon::now()->subDays(rand(60, 90));
            $initialStock = $product->stok_tersedia + rand(50, 200); // More than current stock
            
            InventoryLog::create([
                'product_id' => $product->id,
                'user_id' => $gudangUsers->random()->id,
                'type' => 'in',
                'quantity' => $initialStock,
                'stock_before' => 0,
                'stock_after' => $initialStock,
                'reference_type' => 'initial_stock',
                'reference_id' => null,
                'notes' => "Stok awal {$product->nama_barang} dari supplier",
                'created_at' => $initialDate,
                'updated_at' => $initialDate,
            ]);
        }
    }
    
    private function createRegularStockMovements($products, $gudangUsers)
    {
        // Create stock movements for the last 60 days
        for ($i = 60; $i >= 1; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends for stock movements
            if ($date->isWeekend()) {
                continue;
            }
            
            // 1-3 stock movements per day
            $movementCount = rand(1, 3);
            
            for ($j = 0; $j < $movementCount; $j++) {
                $product = $products->random();
                $user = $gudangUsers->random();
                
                // Random time during business hours
                $movementTime = $date->copy()
                    ->setHour(rand(8, 17))
                    ->setMinute(rand(0, 59))
                    ->setSecond(rand(0, 59));
                
                // Determine movement type (70% in, 30% adjustment)
                $type = rand(1, 10) <= 7 ? 'in' : 'adjustment';
                
                if ($type === 'in') {
                    $this->createStockInMovement($product, $user, $movementTime);
                } else {
                    $this->createStockAdjustmentMovement($product, $user, $movementTime);
                }
            }
        }
    }
    
    private function createStockInMovement($product, $user, $movementTime)
    {
        $quantity = rand(10, 100);
        $stockBefore = $product->stok_tersedia;
        $stockAfter = $stockBefore + $quantity;
        
        InventoryLog::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'in',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => 'restock',
            'reference_id' => null,
            'notes' => $this->getRandomStockInNote($product->nama_barang),
            'created_at' => $movementTime,
            'updated_at' => $movementTime,
        ]);
        
        // Update product stock
        $product->increment('stok_tersedia', $quantity);
    }
    
    private function createStockAdjustmentMovement($product, $user, $movementTime)
    {
        // Adjustment can be positive or negative
        $isPositive = rand(1, 10) <= 6; // 60% positive adjustment
        $maxAdjustment = min(10, $product->stok_tersedia);
        
        if ($maxAdjustment <= 0) return;
        
        $quantity = $isPositive ? rand(1, 10) : -rand(1, $maxAdjustment);
        $stockBefore = $product->stok_tersedia;
        $stockAfter = max(0, $stockBefore + $quantity);
        
        InventoryLog::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'type' => 'adjustment',
            'quantity' => abs($quantity),
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => 'stock_adjustment',
            'reference_id' => null,
            'notes' => $this->getRandomAdjustmentNote($product->nama_barang, $isPositive),
            'created_at' => $movementTime,
            'updated_at' => $movementTime,
        ]);
        
        // Update product stock
        $product->update(['stok_tersedia' => $stockAfter]);
    }
    
    private function createStockAdjustments($products, $gudangUsers)
    {
        // Create some specific stock adjustments for products with issues
        $lowStockProducts = $products->where('stok_tersedia', '<=', 5);
        
        foreach ($lowStockProducts as $product) {
            // 50% chance of emergency restock
            if (rand(1, 10) <= 5) {
                $user = $gudangUsers->random();
                $adjustmentTime = Carbon::now()->subDays(rand(1, 7));
                
                $quantity = rand(20, 50);
                $stockBefore = $product->stok_tersedia;
                $stockAfter = $stockBefore + $quantity;
                
                InventoryLog::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => 'emergency_restock',
                    'reference_id' => null,
                    'notes' => "Emergency restock {$product->nama_barang} karena stok kritis",
                    'created_at' => $adjustmentTime,
                    'updated_at' => $adjustmentTime,
                ]);
                
                $product->increment('stok_tersedia', $quantity);
            }
        }
    }
    
    private function getRandomStockInNote($productName): string
    {
        $notes = [
            "Restock {$productName} dari supplier utama",
            "Pengiriman {$productName} sesuai jadwal",
            "Stok {$productName} masuk dari gudang pusat",
            "Pembelian {$productName} untuk memenuhi demand",
            "Transfer {$productName} dari cabang lain",
            "Restok {$productName} untuk persiapan promosi",
            "Pengadaan {$productName} bulanan",
            "Stok {$productName} tambahan untuk weekend",
            "Restock {$productName} karena fast moving",
            "Pengiriman {$productName} dari distributor",
        ];
        
        return $notes[array_rand($notes)];
    }
    
    private function getRandomAdjustmentNote($productName, $isPositive): string
    {
        if ($isPositive) {
            $notes = [
                "Koreksi stok {$productName} setelah stock opname",
                "Penyesuaian {$productName} karena kesalahan pencatatan",
                "Temuan {$productName} di area storage",
                "Koreksi positif {$productName} hasil audit",
                "Penyesuaian {$productName} setelah verifikasi fisik",
            ];
        } else {
            $notes = [
                "Koreksi stok {$productName} karena kerusakan",
                "Penyesuaian {$productName} akibat expired",
                "Koreksi negatif {$productName} hasil stock opname",
                "Pengurangan {$productName} karena quality issue",
                "Adjustment {$productName} karena kehilangan",
                "Koreksi {$productName} setelah audit internal",
            ];
        }
        
        return $notes[array_rand($notes)];
    }
}
