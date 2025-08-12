<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Delivery;
use App\Models\DeliveryTracking;
use App\Models\K3Checklist;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supirUsers = User::where('role', 'supir')->get();
        $gudangUsers = User::where('role', 'gudang')->get();

        // Get orders that should have deliveries (ready, assigned, shipped, delivered)
        $orders = Order::whereIn('status', ['ready', 'assigned', 'shipped', 'delivered'])
                      ->with('customer')
                      ->get();

        foreach ($orders as $order) {
            $supir = $supirUsers->random();
            $gudang = $gudangUsers->random();

            // Determine delivery status based on order status
            $deliveryStatus = $this->mapOrderStatusToDeliveryStatus($order->status);

            $assignedAt = $order->confirmed_at ?
                $order->confirmed_at->copy()->addHours(rand(1, 6)) :
                $order->created_at->copy()->addHours(rand(2, 8));

            $delivery = Delivery::create([
                'order_id' => $order->id,
                'driver_id' => $supir->id,
                'assigned_by' => $gudang->id,
                'rute_kota' => $this->extractCity($order->customer->alamat),
                'status' => $deliveryStatus,
                'assigned_at' => $assignedAt,
                'k3_checked_at' => in_array($deliveryStatus, ['k3_checked', 'in_progress', 'delivered']) ?
                    $assignedAt->copy()->addMinutes(rand(30, 120)) : null,
                'started_at' => in_array($deliveryStatus, ['in_progress', 'delivered']) ?
                    $assignedAt->copy()->addHours(rand(2, 4)) : null,
                'delivered_at' => $deliveryStatus === 'delivered' ?
                    $assignedAt->copy()->addHours(rand(6, 24)) : null,
                'delivery_latitude' => $deliveryStatus === 'delivered' ? $order->customer->latitude : null,
                'delivery_longitude' => $deliveryStatus === 'delivered' ? $order->customer->longitude : null,
                'delivery_notes' => $this->getRandomDeliveryNote($deliveryStatus),
                'created_at' => $assignedAt,
                'updated_at' => $assignedAt,
            ]);

            // Create K3 Checklist if delivery has started
            if (in_array($deliveryStatus, ['k3_checked', 'in_progress', 'delivered'])) {
                $this->createK3Checklist($delivery, $supir);
            }

            // Create delivery tracking if in progress or delivered
            if (in_array($deliveryStatus, ['in_progress', 'delivered'])) {
                $this->createDeliveryTracking($delivery, $order);
            }
        }
    }

    private function mapOrderStatusToDeliveryStatus($orderStatus): string
    {
        return match($orderStatus) {
            'ready' => 'assigned',
            'assigned' => collect(['assigned', 'k3_checked'])->random(),
            'shipped' => collect(['k3_checked', 'in_progress'])->random(),
            'delivered' => 'delivered',
            default => 'assigned'
        };
    }

    private function extractCity($address): string
    {
        $cities = [
            'Bogor', 'Cibinong', 'Parung', 'Kemang', 'Sentul',
            'Leuwiliang', 'Jasinga', 'Ciampea', 'Cijeruk',
            'Cigombong', 'Ciawi', 'Megamendung', 'Cisarua'
        ];

        foreach ($cities as $city) {
            if (stripos($address, $city) !== false) {
                return $city;
            }
        }

        return $cities[array_rand($cities)];
    }

    private function createK3Checklist($delivery, $supir)
    {
        K3Checklist::create([
            'delivery_id' => $delivery->id,
            'driver_id' => $supir->id,
            'cek_ban' => rand(0, 1),
            'cek_oli' => rand(0, 1),
            'cek_air_radiator' => rand(0, 1),
            'cek_rem' => rand(0, 1),
            'cek_bbm' => rand(0, 1),
            'cek_terpal' => rand(0, 1),
            'catatan' => $this->getRandomK3Note(),
            'checked_at' => $delivery->k3_checked_at,
            'created_at' => $delivery->k3_checked_at,
            'updated_at' => $delivery->k3_checked_at,
        ]);
    }

    private function createDeliveryTracking($delivery, $order)
    {
        $startTime = $delivery->started_at;
        $endTime = $delivery->delivered_at ?? Carbon::now();

        // Create tracking points every 30-60 minutes during delivery
        $currentTime = $startTime->copy();
        $customerLat = $order->customer->latitude;
        $customerLng = $order->customer->longitude;

        // Starting point (warehouse/depot)
        $warehouseLat = -6.5971; // Bogor area
        $warehouseLng = 106.8060;

        $trackingPoints = [];

        // Start tracking
        $trackingPoints[] = [
            'delivery_id' => $delivery->id,
            'driver_id' => $delivery->driver_id,
            'latitude' => $warehouseLat,
            'longitude' => $warehouseLng,
            'status' => 'started',
            'notes' => 'Memulai perjalanan dari gudang',
            'tracked_at' => $startTime,
            'created_at' => $startTime,
            'updated_at' => $startTime,
        ];

        // Intermediate points
        $steps = rand(3, 8);
        for ($i = 1; $i <= $steps; $i++) {
            $currentTime->addMinutes(rand(30, 60));

            if ($currentTime->gt($endTime)) break;

            // Interpolate position between warehouse and customer
            $progress = $i / ($steps + 1);
            $lat = $warehouseLat + ($customerLat - $warehouseLat) * $progress;
            $lng = $warehouseLng + ($customerLng - $warehouseLng) * $progress;

            // Add some random variance
            $lat += (rand(-20, 20) / 100000);
            $lng += (rand(-20, 20) / 100000);

            $trackingPoints[] = [
                'delivery_id' => $delivery->id,
                'driver_id' => $delivery->driver_id,
                'latitude' => $lat,
                'longitude' => $lng,
                'status' => 'in_transit',
                'notes' => 'Dalam perjalanan ke tujuan',
                'tracked_at' => $currentTime->copy(),
                'created_at' => $currentTime->copy(),
                'updated_at' => $currentTime->copy(),
            ];
        }

        // Arrival point (if delivered)
        if ($delivery->status === 'delivered') {
            $trackingPoints[] = [
                'delivery_id' => $delivery->id,
                'driver_id' => $delivery->driver_id,
                'latitude' => $customerLat,
                'longitude' => $customerLng,
                'status' => 'arrived',
                'notes' => 'Tiba di lokasi tujuan',
                'tracked_at' => $delivery->delivered_at->copy()->subMinutes(15),
                'created_at' => $delivery->delivered_at->copy()->subMinutes(15),
                'updated_at' => $delivery->delivered_at->copy()->subMinutes(15),
            ];

            // Delivery completion
            $trackingPoints[] = [
                'delivery_id' => $delivery->id,
                'driver_id' => $delivery->driver_id,
                'latitude' => $customerLat,
                'longitude' => $customerLng,
                'status' => 'delivered',
                'notes' => 'Pengiriman selesai, barang telah diterima',
                'tracked_at' => $delivery->delivered_at,
                'created_at' => $delivery->delivered_at,
                'updated_at' => $delivery->delivered_at,
            ];
        }

        foreach ($trackingPoints as $point) {
            DeliveryTracking::create($point);
        }
    }

    private function getRandomDeliveryNote($status): ?string
    {
        $notes = [
            'assigned' => [
                'Pengiriman telah diassign ke supir',
                'Siap untuk proses pengiriman',
                'Menunggu konfirmasi K3 checklist',
            ],
            'k3_checked' => [
                'K3 checklist telah selesai, siap berangkat',
                'Kendaraan dalam kondisi baik',
                'Semua persyaratan keselamatan terpenuhi',
            ],
            'in_progress' => [
                'Sedang dalam perjalanan ke tujuan',
                'Estimasi tiba dalam 2-3 jam',
                'Kondisi lalu lintas lancar',
                'Mengalami sedikit kemacetan',
            ],
            'delivered' => [
                'Pengiriman berhasil diselesaikan',
                'Barang diterima dalam kondisi baik',
                'Customer puas dengan layanan',
                'Pengiriman tepat waktu',
            ],
        ];

        $statusNotes = $notes[$status] ?? [''];
        return $statusNotes[array_rand($statusNotes)] ?: null;
    }

    private function getRandomK3Note(): ?string
    {
        $notes = [
            'Semua komponen kendaraan dalam kondisi baik',
            'Perlu pengecekan ulang tekanan ban',
            'Oli mesin masih dalam batas normal',
            'Air radiator perlu ditambah sedikit',
            'Rem berfungsi dengan baik',
            'BBM cukup untuk perjalanan',
            'Terpal dalam kondisi baik',
            'Kendaraan siap untuk pengiriman',
            null,
            null,
        ];

        return $notes[array_rand($notes)];
    }
}
