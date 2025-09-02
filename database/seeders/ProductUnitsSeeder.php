<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductUnitsSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create or find a sample product with multiple units to demonstrate the feature
        $product = Product::firstOrCreate(
            ['kode_item' => 'DEMO-MULTI-001'],
            [
                'nama_barang' => 'Contoh Produk Multi-Satuan',
                'keterangan' => 'Produk ini mendemonstrasikan fitur multiple satuan: Karton, Dus, dan Pcs',
                'jenis' => 'pack', // Keep for backward compatibility
                'harga_jual' => 50000, // Keep for backward compatibility
                'stok_tersedia' => 100, // Keep for backward compatibility
                'stok_minimum' => 10, // Keep for backward compatibility
                'is_active' => true,
                'uses_multiple_units' => true,
            ]
        );

        // Clear existing units if any
        $product->units()->delete();

        // Create multiple units for this product
        $units = [
            [
                'unit_name' => 'Pcs',
                'unit_code' => 'PCS',
                'conversion_value' => 1, // Base unit
                'price_per_unit' => 5000,
                'stock_available' => 1200, // 100 karton x 12 pcs/karton
                'stock_minimum' => 120,
                'is_base_unit' => true,
                'sort_order' => 0,
            ],
            [
                'unit_name' => 'Dus',
                'unit_code' => 'DUS',
                'conversion_value' => 6, // 1 dus = 6 pcs
                'price_per_unit' => 28000, // 6 x 5000 = 30000, tapi beri diskon sedikit
                'stock_available' => 200, // 1200 pcs / 6 = 200 dus
                'stock_minimum' => 20,
                'is_base_unit' => false,
                'sort_order' => 1,
            ],
            [
                'unit_name' => 'Karton',
                'unit_code' => 'KTN',
                'conversion_value' => 12, // 1 karton = 12 pcs
                'price_per_unit' => 55000, // 12 x 5000 = 60000, tapi beri diskon
                'stock_available' => 100, // 1200 pcs / 12 = 100 karton
                'stock_minimum' => 10,
                'is_base_unit' => false,
                'sort_order' => 2,
            ],
        ];

        foreach ($units as $unitData) {
            ProductUnit::create(array_merge($unitData, [
                'product_id' => $product->id,
                'is_active' => true,
            ]));
        }

        $this->command->info('Sample multi-unit product created successfully!');
        $this->command->info('Product: ' . $product->nama_barang);
        $this->command->info('Units: ' . $product->units->pluck('formatted_unit')->join(', '));
    }
}
