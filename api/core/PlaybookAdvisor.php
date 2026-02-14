<?php

class PlaybookAdvisor
{
    public static function shouldHandle(string $message): bool
    {
        $msg = strtolower(trim($message));

        $guideCues = [
            'playbook',
            'sop',
            'process',
            'checklist',
            'how do we',
            'how to',
            'guide',
            'onboarding'
        ];

        $dataCues = [
            'count',
            'summary',
            'how many',
            'show',
            'list',
            'overdue',
            'at risk',
            'task load',
            'remaining hours'
        ];

        $hasGuideCue = false;
        foreach ($guideCues as $cue) {
            if (str_contains($msg, $cue)) {
                $hasGuideCue = true;
                break;
            }
        }

        if (!$hasGuideCue) {
            return false;
        }

        foreach ($dataCues as $cue) {
            if (str_contains($msg, $cue) && !str_contains($msg, 'checklist')) {
                return false;
            }
        }

        return true;
    }

    public static function match(string $message): ?array
    {
        $cfg = require __DIR__ . '/../config/playbook.php';
        $msg = strtolower(trim($message));

        if ($msg === '') {
            return null;
        }

        $bestTopic = null;
        $bestScore = 0;

        foreach (($cfg['keywords'] ?? []) as $topic => $terms) {
            $score = 0;
            foreach ($terms as $term) {
                if (str_contains($msg, strtolower($term))) {
                    $score++;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestTopic = $topic;
            }
        }

        if (!$bestTopic || $bestScore === 0) {
            return null;
        }

        $section = $cfg['sections'][$bestTopic] ?? null;
        if (!$section) {
            return null;
        }

        return [
            'topic' => $bestTopic,
            'title' => $section['title'] ?? ucfirst($bestTopic),
            'summary' => $section['summary'] ?? 'AMC playbook guidance available.',
            'checklist' => $section['checklist'] ?? [],
            'match_score' => $bestScore
        ];
    }

    public static function allTopics(): array
    {
        $cfg = require __DIR__ . '/../config/playbook.php';
        return array_keys($cfg['sections'] ?? []);
    }
}
