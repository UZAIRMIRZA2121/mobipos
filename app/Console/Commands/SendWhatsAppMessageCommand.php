<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendWhatsAppMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:whatsapp-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp reminder messages to customers with pending installments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Starting WhatsApp reminder job...');
            \Illuminate\Support\Facades\Log::info('Starting WhatsApp reminder job...');

            // Find all active installments
            $installments = \App\Models\Installment::with(['customer', 'payments'])->where('status', 'Active')->get();
            $today = now()->day;
            
            $count = 0;
            foreach ($installments as $installment) {
                try {
                    $totalPaid = $installment->down_payment + $installment->payments->sum('amount');
                    $remaining = $installment->total_amount - $totalPaid;

                    if ($remaining <= 0) {
                        $reason = "Skipped Installment #{$installment->id}: Fully paid.";
                        $this->warn($reason);
                        \Illuminate\Support\Facades\Log::info($reason);
                        continue;
                    }

                    if ($installment->payment_day != $today) {
                        $reason = "Skipped Installment #{$installment->id}: Today ({$today}) is not the payment day ({$installment->payment_day}).";
                        $this->warn($reason);
                        \Illuminate\Support\Facades\Log::info($reason);
                        continue;
                    }

                    if (!$installment->customer || empty($installment->customer->phone)) {
                        $reason = "Skipped Installment #{$installment->id}: Customer has no valid phone number.";
                        $this->warn($reason);
                        \Illuminate\Support\Facades\Log::warning($reason);
                        continue;
                    }

                    $msg = "Hello {$installment->customer->name}, this is a gentle reminder that your installment plan for Order #{$installment->order_id} has a pending balance.\n";
                    $msg .= "Remaining Balance: PKR {$remaining}\n";
                    if ($installment->agreed_monthly_amount > 0) {
                        $msg .= "Monthly Installment: PKR {$installment->agreed_monthly_amount}\n";
                        $msg .= "Due Date: {$installment->payment_day} of every month\n";
                    }
                    $msg .= "Thank you!";

                    \App\Services\UltramsgService::sendMessage($installment->user_id, $installment->customer->phone, $msg);
                    $count++;
                    
                    // Format phone for the console log so you can see it converted
                    $displayPhone = $installment->customer->phone;
                    $displayPhone = preg_replace('/[^0-9\+]/', '', $displayPhone);
                    if (substr($displayPhone, 0, 1) === '0') {
                        $displayPhone = '+92' . substr($displayPhone, 1);
                    }

                    $logMsg = "Dispatched reminder to Customer: {$installment->customer->name} (Phone: {$displayPhone}) for Installment #{$installment->id}";
                    $this->info($logMsg);
                    \Illuminate\Support\Facades\Log::info($logMsg);

                } catch (\Exception $e) {
                    $errorMsg = "Error processing reminder for Installment ID {$installment->id}: " . $e->getMessage();
                    $this->error($errorMsg);
                    \Illuminate\Support\Facades\Log::error($errorMsg);
                }
            }

            $this->info("Finished! Dispatched {$count} reminder(s).");
            \Illuminate\Support\Facades\Log::info("Finished WhatsApp reminder job! Dispatched {$count} reminder(s).");
        } catch (\Exception $e) {
            $this->error("Fatal Error in WhatsApp reminder job: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error("Fatal Error in WhatsApp reminder job: " . $e->getMessage());
        }
    }
}
