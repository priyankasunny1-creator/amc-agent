<?php

class IntentResolver
{
    public static function resolve(array $candidates, string $message, array $existingParameters = []): array
    {
        $intentConfig = require __DIR__ . '/../config/intents.php';

        $msg = strtolower($message);

        // Deterministic high-confidence shortcut for threshold AMC prompt.
        if (
            str_contains($msg, 'how many') &&
            str_contains($msg, 'client') &&
            str_contains($msg, 'amc') &&
            (
                str_contains($msg, 'more than') ||
                str_contains($msg, 'above') ||
                str_contains($msg, '%')
            )
        ) {
            return [
                'intent' => 'get_clients_above_amc_usage_threshold',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.95,
                'needs_clarification' => false,
                'clarification' => null
            ];
        }

        $scores = [];

        foreach ($candidates as $rank => $intent) {
            if (!isset($intentConfig[$intent])) {
                continue;
            }

            $score = 0;
            $meta = $intentConfig[$intent];

            if (($meta['tier'] ?? '') === 'A') {
                $score += 3;
            }

            if (!empty($meta['parameters'])) {
                $score += 1;
            }

            // Earlier ranked candidates get a small score bonus.
            $score += max(0, 3 - $rank);

            $descriptionWords = explode(' ', strtolower($meta['description'] ?? ''));
            foreach ($descriptionWords as $word) {
                if (strlen($word) > 4 && str_contains($msg, $word)) {
                    $score += 1;
                }
            }

            $scores[$intent] = $score;
        }

        if (empty($scores)) {
            throw new ApiException('Unable to determine intent for this request.', 422, ['phase' => 'intent_resolution']);
        }

        arsort($scores);
        $finalIntent = array_key_first($scores);
        $scoreValues = array_values($scores);
        $topScore = $scoreValues[0];
        $secondScore = $scoreValues[1] ?? 0;

        $confidence = 'high';
        if ($topScore <= 4) {
            $confidence = 'low';
        } elseif (($topScore - $secondScore) <= 1) {
            $confidence = 'medium';
        }

        $requiredParams = $intentConfig[$finalIntent]['parameters'] ?? [];
        $parameters = $existingParameters;
        $missing = [];

        foreach ($requiredParams as $param) {
            $value = trim((string)($parameters[$param] ?? ''));
            if ($value === '') {
                $missing[] = $param;
            }
        }

        if ($confidence === 'low' || $confidence === 'medium' || !empty($missing)) {
            $promptReason = $confidence === 'low'
                ? 'I am not confident I understood your request.'
                : 'I need one more detail before I can run this query.';

            return [
                'intent' => $finalIntent,
                'parameters' => $parameters,
                'confidence' => $confidence,
                'confidence_score' => round($topScore / max(1, $topScore + $secondScore), 2),
                'needs_clarification' => true,
                'clarification' => [
                    'reason' => $promptReason,
                    'question' => !empty($missing)
                        ? ('Please provide: ' . implode(', ', $missing) . '.')
                        : 'Can you clarify what specific report you want?'
                ],
                'missing_parameters' => $missing
            ];
        }

        return [
            'intent' => $finalIntent,
            'parameters' => $parameters,
            'confidence' => $confidence,
            'confidence_score' => round($topScore / max(1, $topScore + $secondScore), 2),
            'needs_clarification' => false,
            'clarification' => null,
            'missing_parameters' => []
        ];
    }
}
