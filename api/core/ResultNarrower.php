<?php

class ResultNarrower
{
    public static function narrow(array $rows, string $message): array
    {
        if (empty($rows) || trim($message) === '') {
            return $rows;
        }

        // Normalize message into keywords
        $keywords = self::extractKeywords($message);

        if (empty($keywords)) {
            return $rows;
        }

        // 1️⃣ Client-based narrowing
        if (isset($rows[0]['client_name'])) {
            $filtered = array_values(array_filter($rows, function ($row) use ($keywords) {
                $haystack = strtolower($row['client_name']);
                foreach ($keywords as $word) {
                    if (strpos($haystack, $word) !== false) {
                        return true;
                    }
                }
                return false;
            }));

            if (!empty($filtered)) {
                return $filtered;
            }
        }

        // 2️⃣ Assignee-based narrowing
        if (isset($rows[0]['assignee_name'])) {
            $filtered = array_values(array_filter($rows, function ($row) use ($keywords) {
                $haystack = strtolower($row['assignee_name']);
                foreach ($keywords as $word) {
                    if (strpos($haystack, $word) !== false) {
                        return true;
                    }
                }
                return false;
            }));

            if (!empty($filtered)) {
                return $filtered;
            }
        }

        // No narrowing possible
        return $rows;
    }

    private static function extractKeywords(string $message): array
    {
        $message = strtolower($message);

        // Remove filler words
        $stopWords = [
            'what','is','the','for','of','and','to','how','many',
            'show','give','me','are','there','usage','amc','client'
        ];

        $words = preg_split('/\W+/', $message);
        $keywords = [];

        foreach ($words as $word) {
            if (strlen($word) >= 3 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        return array_unique($keywords);
    }
}
