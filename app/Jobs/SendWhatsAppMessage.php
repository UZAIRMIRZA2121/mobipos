<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $url;
    public $params;
    public $userId;

    /**
     * Create a new job instance.
     */
    public function __construct($url, $params, $userId)
    {
        $this->url = $url;
        $this->params = $params;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $response = Http::asForm()->post($this->url, $this->params);
            $to = $this->params['to'] ?? 'Unknown';
            
            if ($response->failed()) {
                Log::error("Ultramsg API Error for User {$this->userId} (To: {$to}): " . $response->body());
            } else {
                Log::info("Ultramsg API Success for User {$this->userId} (To: {$to}): " . $response->body());
                // Increment the counter
                $storeSettings = \App\Models\StoreSetting::where('user_id', $this->userId)->first();
                if ($storeSettings) {
                    $storeSettings->increment('ultramsg_total_sent');
                }
            }
        } catch (\Exception $e) {
            Log::error("Ultramsg Exception for User {$this->userId}: " . $e->getMessage());
        }
    }
}
