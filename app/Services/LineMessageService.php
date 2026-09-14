<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineMessageService
{
    public function send(?string $lineUserId, string $message): bool
    {
        if (!$lineUserId) {
            return false;
        }

        try {
            $response = Http::withToken(
                config('services.line.channel_access_token')
            )->post('https://api.line.me/v2/bot/message/push', [
                'to' => $lineUserId,
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $message,
                    ],
                ],
            ]);

            if ($response->failed()) {
                Log::warning('LINE message failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('LINE message error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}