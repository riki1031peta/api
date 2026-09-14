<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LineAuthController extends Controller
{
    public function connect(Request $request)
    {
        $user = $request->user();

        $state = Str::random(64);

        Cache::put(
            'line_connect:' . $state,
            $user->id,
            now()->addMinutes(10)
        );

        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => config('services.line_login.channel_id'),
            'redirect_uri' => config('services.line_login.redirect_uri'),
            'state' => $state,
            'scope' => 'profile openid',
            'bot_prompt' => 'aggressive',
        ]);

        return redirect(
            'https://access.line.me/oauth2/v2.1/authorize?' . $params
        );
    }

    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect(
                'https://dopa-log.com/mypage?line=cancelled'
            );
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $userId = Cache::pull(
            'line_connect:' . $request->state
        );

        if (!$userId) {
            return redirect(
                'https://dopa-log.com/mypage?line=invalid'
            );
        }

        $tokenResponse = Http::asForm()->post(
            'https://api.line.me/oauth2/v2.1/token',
            [
                'grant_type' => 'authorization_code',
                'code' => $request->code,
                'redirect_uri' => config(
                    'services.line_login.redirect_uri'
                ),
                'client_id' => config(
                    'services.line_login.channel_id'
                ),
                'client_secret' => config(
                    'services.line_login.channel_secret'
                ),
            ]
        );

        if ($tokenResponse->failed()) {
            return redirect(
                'https://dopa-log.com/mypage?line=token_error'
            );
        }

        $accessToken = $tokenResponse->json('access_token');

        $profileResponse = Http::withToken(
            $accessToken
        )->get(
            'https://api.line.me/v2/profile'
        );

        if ($profileResponse->failed()) {
            return redirect(
                'https://dopa-log.com/mypage?line=profile_error'
            );
        }

        $lineUserId = $profileResponse->json('userId');

        $user = User::find($userId);

        if (!$user) {
            return redirect(
                'https://dopa-log.com/mypage?line=user_error'
            );
        }

        $alreadyLinked = User::where(
            'line_user_id',
            $lineUserId
        )
            ->where('id', '!=', $user->id)
            ->exists();

        if ($alreadyLinked) {
            return redirect(
                'https://dopa-log.com/mypage?line=already_used'
            );
        }

        $user->update([
            'line_user_id' => $lineUserId,
        ]);

        return redirect(
            'https://dopa-log.com/mypage?line=success'
        );
    }
}