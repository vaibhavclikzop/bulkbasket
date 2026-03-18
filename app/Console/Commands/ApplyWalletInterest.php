<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ApplyWalletInterest extends Command
{
    protected $signature = 'wallet:apply-interest {--dry : Do not insert, only show what would be done}';
    protected $description = 'Apply 18% annual interest daily for orders past due date + grace days';

   public function handle()
{
    $this->info('🚀 Monthly wallet interest started: ' . now());

    // Safety check (extra protection)
    if (now()->day !== 01 || now()->format('H:i') !== '06:00') {
        $this->warn('⏭️ Not scheduled time, exiting.');
        return;
    }

    $monthlyRate = 0.18 / 12;
    $smsConfig = config('services.smswala');

    $orders = DB::table('orders as o')
        ->join('customers as c', 'o.customer_id', '=', 'c.id')
        ->select(
            'o.id as order_id',
            'o.customer_id',
            'o.intrest_amount',
            'o.intrest_value',
            'c.used_wallet',
            'c.number'
        )
        ->where('o.intrest_amount', '>', 0)
        ->get();

    if ($orders->isEmpty()) {
        $this->info('No overdue orders found.');
        return;
    }

    foreach ($orders as $order) {

        $monthlyInterest = round($order->intrest_amount * $monthlyRate, 2);

        if ($monthlyInterest <= 0) {
            continue;
        }

        $this->info("Order #{$order->order_id} → +{$monthlyInterest}");

        if ($this->option('dry')) {
            continue;
        }

        DB::beginTransaction();

        try { 
            DB::table('orders')
                ->where('id', $order->order_id)
                ->update([
                    'intrest_value' => DB::raw("intrest_value + {$monthlyInterest}"),
                    'updated_at' => now(),
                ]);

            // 2️⃣ Ledger entry (OVERDUE)
            DB::table('ledger')->insert([
                'customer_id' => $order->customer_id,
                'order_id' => $order->order_id,
                'type' => 'DEBIT',
                'category' => 'OVERDUE_INTEREST',
                'amount' => $monthlyInterest,
                'remarks' => 'Monthly overdue interest (18% annually)',
                'created_at' => now(),
            ]);

            // 3️⃣ Update customer overdue wallet
            DB::table('customers')
                ->where('id', $order->customer_id)
                ->update([
                    'used_wallet' => DB::raw("used_wallet + {$monthlyInterest}"),
                    'updated_at' => now(),
                ]);

            DB::commit();

            // 4️⃣ Send overdue SMS
            $number = '91' . preg_replace('/\D+/', '', $order->number);

            $msg = "Overdue Alert: Interest of ₹{$monthlyInterest} added to your account for Order #{$order->order_id}. Please clear dues to avoid further charges. - Bulk Basket India";

            Http::get($smsConfig['url'], [
                'key' => $smsConfig['key'],
                'campaign' => $smsConfig['campaign'],
                'routeid' => $smsConfig['routeid'],
                'type' => 'text',
                'contacts' => $number,
                'senderid' => $smsConfig['sender'],
                'msg' => $msg,
                'template_id' => $smsConfig['templates']['overdue_alert'],
                'pe_id' => $smsConfig['pe_id'],
            ]);

            $this->info("📩 SMS sent to {$number}");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Failed Order #{$order->order_id}: " . $e->getMessage());
        }
    }

    $this->info('✅ Monthly wallet interest completed: ' . now());
}

}
