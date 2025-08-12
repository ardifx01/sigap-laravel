<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesUsers = User::where('role', 'sales')->get();
        
        $customers = [
            // Customers for Sales 1
            [
                'nama_toko' => 'Toko Berkah Jaya',
                'phone' => '081234567801',
                'alamat' => 'Jl. Raya Bogor No. 123, Bogor Timur, Kota Bogor',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 5000000,
                'latitude' => -6.5971,
                'longitude' => 106.8060,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Warung Sari Rasa',
                'phone' => '081234567802',
                'alamat' => 'Jl. Pajajaran No. 45, Bogor Tengah, Kota Bogor',
                'limit_hari_piutang' => 21,
                'limit_amount_piutang' => 3000000,
                'latitude' => -6.5944,
                'longitude' => 106.7969,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Minimarket Sejahtera',
                'phone' => '081234567803',
                'alamat' => 'Jl. Ir. H. Juanda No. 78, Bogor Utara, Kota Bogor',
                'limit_hari_piutang' => 45,
                'limit_amount_piutang' => 8000000,
                'latitude' => -6.5877,
                'longitude' => 106.7982,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Toko Murah Meriah',
                'phone' => '081234567804',
                'alamat' => 'Jl. Raya Dramaga No. 234, Dramaga, Kabupaten Bogor',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 4500000,
                'latitude' => -6.5463,
                'longitude' => 106.7344,
                'is_active' => true,
            ],

            // Customers for Sales 2
            [
                'nama_toko' => 'Warung Bu Siti',
                'phone' => '081234567805',
                'alamat' => 'Jl. Raya Cibinong No. 156, Cibinong, Kabupaten Bogor',
                'limit_hari_piutang' => 14,
                'limit_amount_piutang' => 2000000,
                'latitude' => -6.4817,
                'longitude' => 106.8540,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Toko Keluarga Bahagia',
                'phone' => '081234567806',
                'alamat' => 'Jl. Raya Parung No. 89, Parung, Kabupaten Bogor',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 6000000,
                'latitude' => -6.4219,
                'longitude' => 106.7331,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Minimarket Rezeki',
                'phone' => '081234567807',
                'alamat' => 'Jl. Raya Kemang No. 67, Kemang, Kabupaten Bogor',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 7500000,
                'latitude' => -6.2754,
                'longitude' => 106.9058,
                'is_active' => true,
            ],

            // Customers for Sales 3
            [
                'nama_toko' => 'Warung Pak Budi',
                'phone' => '081234567808',
                'alamat' => 'Jl. Raya Sentul No. 123, Sentul, Kabupaten Bogor',
                'limit_hari_piutang' => 21,
                'limit_amount_piutang' => 3500000,
                'latitude' => -6.5598,
                'longitude' => 106.8747,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Toko Serba Ada Makmur',
                'phone' => '081234567809',
                'alamat' => 'Jl. Raya Leuwiliang No. 45, Leuwiliang, Kabupaten Bogor',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 5500000,
                'latitude' => -6.5539,
                'longitude' => 106.6331,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Warung Ibu Ani',
                'phone' => '081234567810',
                'alamat' => 'Jl. Raya Jasinga No. 78, Jasinga, Kabupaten Bogor',
                'limit_hari_piutang' => 14,
                'limit_amount_piutang' => 2500000,
                'latitude' => -6.5167,
                'longitude' => 106.4667,
                'is_active' => true,
            ],

            // Customers for Sales 4
            [
                'nama_toko' => 'Minimarket Harapan Jaya',
                'phone' => '081234567811',
                'alamat' => 'Jl. Raya Ciampea No. 234, Ciampea, Kabupaten Bogor',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 6500000,
                'latitude' => -6.5547,
                'longitude' => 106.7008,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Toko Sembako Berkah',
                'phone' => '081234567812',
                'alamat' => 'Jl. Raya Cijeruk No. 56, Cijeruk, Kabupaten Bogor',
                'limit_hari_piutang' => 21,
                'limit_amount_piutang' => 4000000,
                'latitude' => -6.5333,
                'longitude' => 106.7167,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Warung Maju Bersama',
                'phone' => '081234567813',
                'alamat' => 'Jl. Raya Cigombong No. 123, Cigombong, Kabupaten Bogor',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 3800000,
                'latitude' => -6.6167,
                'longitude' => 106.8333,
                'is_active' => true,
            ],

            // Customers for Sales 5
            [
                'nama_toko' => 'Toko Kebutuhan Sehari-hari',
                'phone' => '081234567814',
                'alamat' => 'Jl. Raya Ciawi No. 89, Ciawi, Kabupaten Bogor',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 7000000,
                'latitude' => -6.6667,
                'longitude' => 106.8500,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Minimarket Sumber Rejeki',
                'phone' => '081234567815',
                'alamat' => 'Jl. Raya Megamendung No. 67, Megamendung, Kabupaten Bogor',
                'limit_hari_piutang' => 21,
                'limit_amount_piutang' => 4200000,
                'latitude' => -6.7000,
                'longitude' => 106.9333,
                'is_active' => true,
            ],
            [
                'nama_toko' => 'Warung Pak Haji',
                'phone' => '081234567816',
                'alamat' => 'Jl. Raya Cisarua No. 145, Cisarua, Kabupaten Bogor',
                'limit_hari_piutang' => 14,
                'limit_amount_piutang' => 2800000,
                'latitude' => -6.6833,
                'longitude' => 106.9500,
                'is_active' => true,
            ],

            // Inactive customers
            [
                'nama_toko' => 'Toko Tutup Sementara',
                'phone' => '081234567817',
                'alamat' => 'Jl. Raya Sukabumi No. 999, Sukabumi',
                'limit_hari_piutang' => 30,
                'limit_amount_piutang' => 1000000,
                'latitude' => -6.9175,
                'longitude' => 106.9266,
                'is_active' => false,
            ],
        ];

        foreach ($customers as $index => $customer) {
            // Assign customers to sales users in round-robin fashion
            $salesIndex = $index % $salesUsers->count();
            $customer['sales_id'] = $salesUsers[$salesIndex]->id;
            
            Customer::create($customer);
        }
    }
}
