<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Order;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get delivered orders that should have payments
        $deliveredOrders = Order::where('status', 'delivered')
                               ->with('customer')
                               ->get();
        
        $notaCounter = 1;
        
        foreach ($deliveredOrders as $order) {
            // 90% of delivered orders have payment records
            if (rand(1, 10) <= 9) {
                $deliveredDate = $order->delivered_at;
                $paymentMethod = $this->getRandomPaymentMethod();
                
                // Payment is usually created 1-3 days after delivery
                $paymentCreatedAt = $deliveredDate->copy()->addDays(rand(1, 3));
                
                // Due date is usually 14-30 days from creation
                $dueDate = $paymentCreatedAt->copy()->addDays(rand(14, 30));
                
                // Determine payment status and amount paid
                $paymentStatus = $this->determinePaymentStatus($paymentCreatedAt, $dueDate);
                $totalBill = $order->total_amount;
                $amountPaid = $this->calculateAmountPaid($totalBill, $paymentStatus);
                
                $payment = Payment::create([
                    'nomor_nota' => 'NOTA-' . $paymentCreatedAt->format('Ymd') . '-' . str_pad($notaCounter, 4, '0', STR_PAD_LEFT),
                    'order_id' => $order->id,
                    'sales_id' => $order->sales_id,
                    'jumlah_tagihan' => $totalBill,
                    'jumlah_bayar' => $amountPaid,
                    'jenis_pembayaran' => $paymentMethod,
                    'bukti_transfer' => $paymentMethod === 'transfer' ? 'payments/transfer_' . $order->id . '.jpg' : null,
                    'status' => $paymentStatus,
                    'tanggal_jatuh_tempo' => $dueDate,
                    'tanggal_bayar' => $paymentStatus === 'lunas' ? 
                        $paymentCreatedAt->copy()->addDays(rand(0, ($dueDate->diffInDays($paymentCreatedAt) - 1))) : 
                        null,
                    'catatan' => $this->getRandomPaymentNote($paymentStatus, $paymentMethod),
                    'created_at' => $paymentCreatedAt,
                    'updated_at' => $paymentCreatedAt,
                ]);
                
                $notaCounter++;
            }
        }
        
        // Create some additional payments for older orders (partial payments, etc.)
        $this->createAdditionalPayments($notaCounter);
    }
    
    private function getRandomPaymentMethod(): string
    {
        $methods = ['tunai', 'transfer', 'giro'];
        $weights = [50, 40, 10]; // 50% tunai, 40% transfer, 10% giro
        
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($weights as $index => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $methods[$index];
            }
        }
        
        return 'tunai';
    }
    
    private function determinePaymentStatus($createdAt, $dueDate): string
    {
        $now = Carbon::now();
        $daysSinceCreated = $createdAt->diffInDays($now);
        $isOverdue = $now->gt($dueDate);
        
        if ($isOverdue) {
            // 30% of overdue payments are still unpaid
            return rand(1, 10) <= 3 ? 'overdue' : 'lunas';
        } elseif ($daysSinceCreated <= 7) {
            // Recent payments - 60% paid, 40% unpaid
            return rand(1, 10) <= 6 ? 'lunas' : 'belum_lunas';
        } else {
            // Older payments - 80% paid, 20% unpaid
            return rand(1, 10) <= 8 ? 'lunas' : 'belum_lunas';
        }
    }
    
    private function calculateAmountPaid($totalBill, $status): float
    {
        switch ($status) {
            case 'lunas':
                return $totalBill;
            case 'belum_lunas':
                // Sometimes partial payment
                return rand(1, 10) <= 3 ? $totalBill * (rand(20, 80) / 100) : 0;
            case 'overdue':
                // Often partial payment
                return rand(1, 10) <= 6 ? $totalBill * (rand(10, 90) / 100) : 0;
            default:
                return 0;
        }
    }
    
    private function createAdditionalPayments($startCounter)
    {
        // Create some payments for orders that might not be delivered yet
        $confirmedOrders = Order::whereIn('status', ['confirmed', 'ready', 'assigned', 'shipped'])
                                ->where('created_at', '<=', Carbon::now()->subDays(7))
                                ->get();
        
        $notaCounter = $startCounter;
        
        foreach ($confirmedOrders as $order) {
            // 30% chance of advance payment
            if (rand(1, 10) <= 3) {
                $orderDate = $order->created_at;
                $paymentCreatedAt = $orderDate->copy()->addDays(rand(1, 5));
                
                Payment::create([
                    'nomor_nota' => 'NOTA-' . $paymentCreatedAt->format('Ymd') . '-' . str_pad($notaCounter, 4, '0', STR_PAD_LEFT),
                    'order_id' => $order->id,
                    'sales_id' => $order->sales_id,
                    'jumlah_tagihan' => $order->total_amount,
                    'jumlah_bayar' => 0,
                    'jenis_pembayaran' => $this->getRandomPaymentMethod(),
                    'status' => 'belum_lunas',
                    'tanggal_jatuh_tempo' => $paymentCreatedAt->copy()->addDays(30),
                    'tanggal_bayar' => null,
                    'catatan' => 'Invoice untuk pesanan yang sedang diproses',
                    'created_at' => $paymentCreatedAt,
                    'updated_at' => $paymentCreatedAt,
                ]);
                
                $notaCounter++;
            }
        }
    }
    
    private function getRandomPaymentNote($status, $method): ?string
    {
        $notes = [
            'lunas' => [
                'Pembayaran lunas tepat waktu',
                'Terima kasih atas pembayaran yang cepat',
                'Pembayaran diterima dengan baik',
                'Customer sangat kooperatif dalam pembayaran',
                'Pembayaran sesuai dengan kesepakatan',
            ],
            'belum_lunas' => [
                'Menunggu konfirmasi pembayaran',
                'Customer meminta perpanjangan waktu',
                'Sedang dalam proses pembayaran',
                'Akan dibayar minggu depan',
                'Menunggu persetujuan dari atasan',
            ],
            'overdue' => [
                'Pembayaran terlambat, perlu follow up',
                'Customer mengalami kendala cash flow',
                'Sudah diingatkan beberapa kali',
                'Perlu koordinasi dengan tim collection',
                'Customer berjanji bayar akhir bulan',
            ],
        ];
        
        $methodNotes = [
            'transfer' => 'Pembayaran via transfer bank',
            'tunai' => 'Pembayaran secara tunai',
            'giro' => 'Pembayaran menggunakan giro',
        ];
        
        $statusNotes = $notes[$status] ?? [''];
        $baseNote = $statusNotes[array_rand($statusNotes)];
        
        // Sometimes add method note
        if (rand(1, 10) <= 3) {
            $baseNote .= '. ' . $methodNotes[$method];
        }
        
        return $baseNote ?: null;
    }
}
