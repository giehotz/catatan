#!/usr/bin/env php
<?php

if (PHP_VERSION_ID < 70400) {
    die("PHP 7.4+ required for this script\n");
}

$label = $argv[1] ?? 'baseline';

echo "Preload Benchmark: $label\n";
echo "PHP: " . PHP_VERSION . " on " . php_uname('s') . "\n\n";

$start = microtime(true);
$peakBefore = memory_get_peak_usage(true);

require __DIR__ . '/../preload.php';

$duration = (microtime(true) - $start) * 1000;
$peakAfter = memory_get_peak_usage(true);
$memoryUsed = ($peakAfter - $peakBefore) / 1024 / 1024;

$results = [
    'label'        => $label,
    'timestamp'    => date('Y-m-d H:i:s'),
    'environment'  => [
        'php_version'       => PHP_VERSION,
        'platform'          => php_uname('s'),
        'php_memory_limit'  => ini_get('memory_limit'),
    ],
    'execution_ms'   => round($duration, 2),
    'memory_mb'      => round($memoryUsed, 2),
];

$filename = "scripts/benchmark-preload-{$label}.json";
file_put_contents($filename, json_encode($results, JSON_PRETTY_PRINT));

echo "Execution: {$results['execution_ms']} ms\n";
echo "Memory:    {$results['memory_mb']} MB\n";
echo "Saved: $filename\n";
