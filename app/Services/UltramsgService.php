<?php

namespace App\Services;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UltramsgService
{
    /**
     * Send a WhatsApp message using Ultramsg
     *
     * @param int $userId The store owner's user ID
     * @param string $to Phone number (preferably with country code, e.g., +923001234567)
     * @param string $body The message text
     * @return bool
     */
    public static function sendMessage($userId, $to, $body)
    {
        $settings = StoreSetting::where('user_id', $userId)->first();

        if (!$settings || empty($settings->ultramsg_instance_id) || empty($settings->ultramsg_token)) {
            Log::info("Ultramsg credentials not set for user ID: {$userId}");
            return false;
        }

        if (!$settings->whatsapp_config) {
            Log::info("WhatsApp config is disabled for user ID: {$userId}");
            return false;
        }

        $to = preg_replace('/[^0-9\+]/', '', $to);
        if (substr($to, 0, 1) === '0') {
            $to = '+92' . substr($to, 1);
        } else if (substr($to, 0, 2) === '92') {
            $to = '+' . $to;
        } else if (substr($to, 0, 1) !== '+') {
             if(strlen($to) == 10) {
                 $to = '+92' . $to;
             } else {
                 $to = '+' . $to;
             }
        }

        $instanceId = $settings->ultramsg_instance_id;
        $token = $settings->ultramsg_token;

        $url = $settings->ultramsg_api_url;
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $url = "https://api.ultramsg.com/{$instanceId}/messages/chat";
        }
        
        $params = array(
            'token' => $token,
            'to' => $to,
            'body' => $body
        );

        \App\Jobs\SendWhatsAppMessage::dispatchSync($url, $params, $userId);

        return true;
    }
}
