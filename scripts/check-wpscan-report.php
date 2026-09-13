<?php

declare(strict_types=1);

$path = $argv[1] ?? '';
if ($path === '' || ! is_readable($path)) {
    fwrite(STDERR, "Usage: php scripts/check-wpscan-report.php <report.json>\n");
    exit(2);
}

$decoded = json_decode((string) file_get_contents($path), true);
if (! is_array($decoded)) {
    fwrite(STDERR, "WPScan report is not valid JSON.\n");
    exit(2);
}

/** @param mixed $value */
function jp_count_wpscan_vulnerabilities(mixed $value): int
{
    if (! is_array($value)) {
        return 0;
    }

    $count = 0;
    foreach ($value as $key => $child) {
        if ($key === 'vulnerabilities' && is_array($child)) {
            $count += count($child);
            continue;
        }

        $count += jp_count_wpscan_vulnerabilities($child);
    }

    return $count;
}

$count = jp_count_wpscan_vulnerabilities($decoded);
if ($count > 0) {
    fwrite(STDERR, sprintf("WPScan reported %d known vulnerability record(s).\n", $count));
    exit(1);
}

echo "WPScan report contains no known vulnerability records.\n";
