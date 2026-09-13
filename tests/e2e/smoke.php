<?php

declare(strict_types=1);

$baseUrl = getenv('JP_E2E_BASE_URL') ?: '';
if ($baseUrl === '') {
    echo "SKIP e2e smoke: set JP_E2E_BASE_URL to a running Jouvence Para environment.\n";
    exit(0);
}

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true,
        'header' => "User-Agent: JouvencePara-E2E/1.0\r\n",
    ],
]);

$body = @file_get_contents(rtrim($baseUrl, '/') . '/', false, $context);
$statusLine = $http_response_header[0] ?? '';

if ($body === false || preg_match('/^HTTP\/\S+\s+([23]\d\d)\b/', $statusLine) !== 1) {
    fwrite(STDERR, "FAIL e2e smoke: unexpected response: $statusLine\n");
    exit(1);
}

if (stripos($body, '<html') === false) {
    fwrite(STDERR, "FAIL e2e smoke: response does not look like HTML.\n");
    exit(1);
}

echo "PASS e2e smoke: $statusLine\n";
