<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Minuman
            [
                'kode_item' => 'MNM001',
                'nama_barang' => 'Aqua Botol 600ml',
                'keterangan' => 'Air mineral dalam kemasan botol 600ml',
                'jenis' => 'dus',
                'harga_jual' => 48000,
                'stok_tersedia' => 150,
                'stok_minimum' => 20,
                'is_active' => true,
            ],
            [
                'kode_item' => 'MNM002',
                'nama_barang' => 'Coca Cola Kaleng 330ml',
                'keterangan' => 'Minuman berkarbonasi rasa cola',
                'jenis' => 'dus',
                'harga_jual' => 72000,
                'stok_tersedia' => 80,
                'stok_minimum' => 15,
                'is_active' => true,
            ],
            [
                'kode_item' => 'MNM003',
                'nama_barang' => 'Teh Botol Sosro 450ml',
                'keterangan' => 'Teh manis dalam kemasan botol',
                'jenis' => 'dus',
                'harga_jual' => 54000,
                'stok_tersedia' => 120,
                'stok_minimum' => 25,
                'is_active' => true,
            ],
            [
                'kode_item' => 'MNM004',
                'nama_barang' => 'Pocari Sweat Botol 500ml',
                'keterangan' => 'Minuman isotonik pengganti ion tubuh',
                'jenis' => 'dus',
                'harga_jual' => 84000,
                'stok_tersedia' => 60,
                'stok_minimum' => 10,
                'is_active' => true,
            ],
            [
                'kode_item' => 'MNM005',
                'nama_barang' => 'Fanta Orange Kaleng 330ml',
                'keterangan' => 'Minuman berkarbonasi rasa jeruk',
                'jenis' => 'dus',
                'harga_jual' => 72000,
                'stok_tersedia' => 8,
                'stok_minimum' => 20,
                'is_active' => true,
            ],

            // Makanan Ringan
            [
                'kode_item' => 'SNK001',
                'nama_barang' => 'Chitato Rasa Sapi Panggang',
                'keterangan' => 'Keripik kentang rasa sapi panggang 68g',
                'jenis' => 'dus',
                'harga_jual' => 180000,
                'stok_tersedia' => 45,
                'stok_minimum' => 12,
                'is_active' => true,
            ],
            [
                'kode_item' => 'SNK002',
                'nama_barang' => 'Indomie Goreng',
                'keterangan' => 'Mie instan rasa ayam bawang',
                'jenis' => 'dus',
                'harga_jual' => 90000,
                'stok_tersedia' => 200,
                'stok_minimum' => 30,
                'is_active' => true,
            ],
            [
                'kode_item' => 'SNK003',
                'nama_barang' => 'Oreo Original',
                'keterangan' => 'Biskuit sandwich cokelat',
                'jenis' => 'dus',
                'harga_jual' => 156000,
                'stok_tersedia' => 75,
                'stok_minimum' => 18,
                'is_active' => true,
            ],
            [
                'kode_item' => 'SNK004',
                'nama_barang' => 'Tango Wafer Cokelat',
                'keterangan' => 'Wafer berlapis cokelat',
                'jenis' => 'dus',
                'harga_jual' => 120000,
                'stok_tersedia' => 90,
                'stok_minimum' => 20,
                'is_active' => true,
            ],
            [
                'kode_item' => 'SNK005',
                'nama_barang' => 'Pringles Original',
                'keterangan' => 'Keripik kentang dalam tabung 107g',
                'jenis' => 'dus',
                'harga_jual' => 240000,
                'stok_tersedia' => 4,
                'stok_minimum' => 15,
                'is_active' => true,
            ],

            // Produk Susu
            [
                'kode_item' => 'MLK001',
                'nama_barang' => 'Ultra Milk Full Cream 1L',
                'keterangan' => 'Susu UHT full cream kemasan 1 liter',
                'jenis' => 'dus',
                'harga_jual' => 180000,
                'stok_tersedia' => 65,
                'stok_minimum' => 15,
                'is_active' => true,
            ],
            [
                'kode_item' => 'MLK002',
                'nama_barang' => 'Dancow Fortigro Cokelat',
                'keterangan' => 'Susu bubuk rasa cokelat 800g',
                'jenis' => 'dus',
                'harga_jual' => 420000,
                'stok_tersedia' => 6,
                'stok_minimum' => 18,
                'is_active' => true,
            ],
            [
                'kode_item' => 'MLK003',
                'nama_barang' => 'Yakult Original',
                'keterangan' => 'Minuman probiotik kemasan 5 botol',
                'jenis' => 'pack',
                'harga_jual' => 12000,
                'stok_tersedia' => 180,
                'stok_minimum' => 40,
                'is_active' => true,
            ],

            // Produk Kecantikan
            [
                'kode_item' => 'BTY001',
                'nama_barang' => 'Ponds Age Miracle Day Cream',
                'keterangan' => 'Krim wajah anti aging siang hari 50g',
                'jenis' => 'pack',
                'harga_jual' => 85000,
                'stok_tersedia' => 40,
                'stok_minimum' => 10,
                'is_active' => true,
            ],
            [
                'kode_item' => 'BTY002',
                'nama_barang' => 'Wardah Lightening Day Cream',
                'keterangan' => 'Krim pencerah wajah siang hari 30g',
                'jenis' => 'pack',
                'harga_jual' => 45000,
                'stok_tersedia' => 7,
                'stok_minimum' => 25,
                'is_active' => true,
            ],

            // Produk Kebersihan
            [
                'kode_item' => 'CLN001',
                'nama_barang' => 'Rinso Anti Noda Cair 800ml',
                'keterangan' => 'Deterjen cair anti noda',
                'jenis' => 'pack',
                'harga_jual' => 28000,
                'stok_tersedia' => 85,
                'stok_minimum' => 20,
                'is_active' => true,
            ],
            [
                'kode_item' => 'CLN002',
                'nama_barang' => 'Sunlight Jeruk Nipis 800ml',
                'keterangan' => 'Sabun cuci piring rasa jeruk nipis',
                'jenis' => 'pack',
                'harga_jual' => 18000,
                'stok_tersedia' => 110,
                'stok_minimum' => 25,
                'is_active' => true,
            ],
            [
                'kode_item' => 'CLN003',
                'nama_barang' => 'Molto Ultra Sekali Bilas 800ml',
                'keterangan' => 'Pelembut dan pewangi pakaian',
                'jenis' => 'pack',
                'harga_jual' => 22000,
                'stok_tersedia' => 0,
                'stok_minimum' => 15,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
