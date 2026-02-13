<?php

class OpenAIClient
{
    /**
     * Calls OpenAI Responses API and returns RAW TEXT output
     * Used ONLY for intent classification (STEP 11)
     */
    public static function classify(string $systemPrompt, string $userMessage): string
    {
        $apiKey = getenv('OPENAI_API_KEY');

        if (!$apiKey) {
            throw new Exception('OPENAI_API_KEY not set');
        }

        $payload = [
            'model' => 'gpt-4.1-mini',
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
            'temperature' => 0
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

        // ✅ Correct Responses API parsing
        if (!isset($decoded['output'][0]['content'][0]['text'])) {
            throw new Exception(
                'OpenAI returned empty or unexpected response: ' . $raw
            );
        }

        return trim($decoded['output'][0]['content'][0]['text']);
    }
}
