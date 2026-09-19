<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function recommend(string $message, array $blogs): array
    {
        $prompt = <<<PROMPT
            あなたはブログサイト「dopa-log」の記事推薦AIです。

            ユーザーの今の気分・テンション・発言内容を読み取り、
            候補記事の中から「今この人が読むとよさそうな記事」を必ず1つ選んでください。

            ユーザーの発言:
            {$message}

            候補記事:
            PROMPT;

        foreach ($blogs as $blog) {
            $prompt .= "\n"
                . "ID: {$blog['id']}\n"
                . "タイトル: {$blog['title']}\n"
                . "カテゴリ: {$blog['category']}\n"
                . "本気度: {$blog['seriousness']}\n"
                . "内容: {$blog['content']}\n";
        }

        $response = Http::retry(
            3,
            function (int $attempt) {
                return 1000 * (2 ** ($attempt - 1));
            },
            throw: false
        )->withHeaders([
            'x-goog-api-key' => config('services.gemini.api_key'),
        ])->post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent',
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'blog_id' => [
                                'type' => 'integer',
                            ],
                            'message' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => [
                            'blog_id',
                            'message',
                        ],
                    ],
                ],
            ]
        );

        $response->throw();

        $text = $response->json(
            'candidates.0.content.parts.0.text'
        );

        return json_decode($text, true);
    }
}