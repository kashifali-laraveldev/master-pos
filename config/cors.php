<?php

/**
 * Fruitcake CORS expects valid PCRE patterns with delimiters.
 * This normalizer allows writing plain patterns in .env and wraps them safely.
 */
$patternList = array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGIN_PATTERNS', ''))));
$normalizedPatterns = array_map(static function (string $pattern): string {
    if ($pattern === '') {
        return $pattern;
    }

    $first = $pattern[0];
    $last = substr($pattern, -1);
    $hasDelimiter = ! ctype_alnum($first) && $first === $last;

    return $hasDelimiter ? $pattern : '#' . str_replace('#', '\#', $pattern) . '#';
}, $patternList);

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173')))),
    'allowed_origins_patterns' => array_values(array_filter($normalizedPatterns)),
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];

