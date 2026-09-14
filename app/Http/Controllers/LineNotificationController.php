<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class LineNotificationController extends Controller
{
    public function test()
    {
        $response = Http::withToken(
            config('services.line.channel_access_token')
        )->post('https://api.line.me/v2/bot/message/push', [
            'to' => config('services.line.test_user_id'),
            'messages' => [
                [
                    'type' => 'text',
                    'text' => "dopa-logからテスト通知です！\nLINE連携成功！",
                ],
            ],
        ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'LINE通知に失敗しました。',
                'status' => $response->status(),
                'error' => $response->json(),
            ], 500);
        }

        return response()->json([
            'message' => 'LINE通知を送信しました。',
        ]);
    }
}