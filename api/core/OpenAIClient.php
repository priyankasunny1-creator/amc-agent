<?php

class OpenAIClient
{
    public static function classify(string $systemPrompt, string $userMessage): string
    {
        $apiKey = getenv('OPENAI_API_KEY');

        if (!$apiKey) {
            throw new Exception('OPENAI_API_KEY not set');
        }

        $cfg = require __DIR__ . '/../config/openai.php';

        $payload = [
            'model' => $cfg['model'] ?? 'gpt-4.1-mini',
            'input' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ],
            'temperature' => $cfg['temperature'] ?? 0,
            'max_output_tokens' => $cfg['max_tokens'] ?? 300
        ];

        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $raw = curl_exec($ch);

        if ($raw === false) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);

        $decoded = json_decode($raw, true);

        if (!isset($decoded['output'][0]['content'][0]['text'])) {
            throw new Exception('OpenAI returned empty or unexpected response: ' . $raw);
        }

        return trim($decoded['output'][0]['content'][0]['text']);
    }
}
