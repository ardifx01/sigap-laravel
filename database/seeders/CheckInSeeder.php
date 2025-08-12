<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CheckIn;
use App\Models\User;
use App\Models\Customer;
use Carbon\Carbon;

class CheckInSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesUsers = User::where('role', 'sales')->get();
        
        // Generate check-ins for the last 30 days
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            // Skip weekends for more realistic data
            if ($date->isWeekend()) {
                continue;
            }
            
            foreach ($salesUsers as $sales) {
                $customers = Customer::where('sales_id', $sales->id)
                                   ->where('is_active', true)
                                   ->get();
                
                // Each sales visits 1-3 customers per day randomly
                $visitCount = rand(1, 3);
                $visitedCustomers = $customers->random(min($visitCount, $customers->count()));
                
                foreach ($visitedCustomers as $customer) {
                    // Random time between 8 AM and 5 PM
                    $checkInTime = $date->copy()
                        ->setHour(rand(8, 17))
                        ->setMinute(rand(0, 59))
                        ->setSecond(rand(0, 59));
                    
                    // Add some GPS variance around customer location
                    $latVariance = (rand(-50, 50) / 100000); // ~50 meter variance
                    $lngVariance = (rand(-50, 50) / 100000);
                    
                    CheckIn::create([
                        'sales_id' => $sales->id,
                        'customer_id' => $customer->id,
                        'latitude' => $customer->latitude + $latVariance,
                        'longitude' => $customer->longitude + $lngVariance,
                        'foto_selfie' => 'check-ins/selfie_' . $sales->id . '_' . $customer->id . '_' . $date->format('Ymd') . '.jpg',
                        'catatan' => $this->getRandomCheckInNote(),
                        'checked_in_at' => $checkInTime,
                        'created_at' => $checkInTime,
                        'updated_at' => $checkInTime,
                    ]);
                }
            }
        }
    }
    
    private function getRandomCheckInNote(): string
    {
        $notes = [
            'Kunjungan rutin, toko ramai pembeli',
            'Pengecekan stok dan diskusi produk baru',
            'Follow up pesanan minggu lalu',
            'Toko sedang sepi, owner sedang keluar',
            'Diskusi program promosi bulan depan',
            'Penagihan dan pembahasan kredit limit',
            'Survei kepuasan pelanggan',
            'Pengenalan produk baru dari supplier',
            'Koordinasi jadwal pengiriman',
            'Toko tutup sementara, akan kembali besok',
            'Meeting dengan owner membahas kerjasama',
            'Cek kondisi display produk di toko',
            'Diskusi target penjualan bulan ini',
            'Toko sedang renovasi, kunjungan singkat',
            'Pembahasan masalah kualitas produk',
            'Koordinasi event promosi weekend',
            'Pengecekan kompetitor di area sekitar',
            'Diskusi strategi pemasaran lokal',
            'Toko baru buka, sambutan sangat baik',
            'Kunjungan maintenance relationship',
        ];
        
        return $notes[array_rand($notes)];
    }
}
