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
            'onboarding',
            'how should we',
            'operations rhythm',
            'weekly and monthly'
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
            'remaining hours',
            'how many',
            'above amc',
            'zero amc'
        ];

        $hasGuideCue = false;
        foreach ($guideCues as $cue) {
            if (str_contains($msg, $cue)) {
                $hasGuideCue = true;
                break;
            }
        }

        $isReportingGuideRequest = (str_contains($msg, 'weekly') || str_contains($msg, 'monthly'))
            && (str_contains($msg, 'report') || str_contains($msg, 'reports'))
            && (str_contains($msg, 'how should') || str_contains($msg, 'how to') || str_contains($msg, 'send'));

        $isDailyOpsGuideRequest = str_contains($msg, 'daily')
            && (str_contains($msg, 'operations rhythm') || str_contains($msg, 'ops rhythm') || str_contains($msg, 'checklist'));

        if (!$hasGuideCue && !$isReportingGuideRequest && !$isDailyOpsGuideRequest) {
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
