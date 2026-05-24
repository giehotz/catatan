#!/usr/bin/env php
<?php

if (PHP_VERSION_ID < 70400) {
    die("PHP 7.4+ required for this script\n");
}

$label = $argv[1] ?? 'baseline';
$url   = $argv[2] ?? 'http://localhost:8080/';
$samples = 10;

echo "Benchmark: $label\n";
echo "URL: $url\n";
echo "Samples: $samples\n\n";

echo "Warming up... ";
for ($i = 0; $i < 3; $i++) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_exec($ch);
    curl_close($ch);
}
echo "done\n";

$responseTimes = [];

for ($i = 0; $i < $samples; $i++) {
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_exec($ch);
    $duration = (microtime(true) - $start) * 1000;
    curl_close($ch);

    $responseTimes[] = $duration;
    echo "  Sample " . ($i + 1) . ": " . round($duration, 2) . " ms\n";
}

sort($responseTimes);
$trimmed = array_slice($responseTimes, 1, -1);

$mean  = array_sum($trimmed) / count($trimmed);
$min   = min($trimmed);
$max   = max($trimmed);

$variance = array_reduce($trimmed,
    fn($sum, $val) => $sum + pow($val - $mean, 2), 0
) / count($trimmed);
$stdDev  = sqrt($variance);
$coeffVar = ($stdDev / $mean) * 100;

sort($responseTimes);
$p95Index = (int) ceil(0.95 * count($responseTimes)) - 1;
$p95 = $responseTimes[$p95Index];

$results = [
    'label'        => $label,
    'url'          => $url,
    'timestamp'    => date('Y-m-d H:i:s'),
    'environment'  => [
        'php_version'       => PHP_VERSION,
        'platform'          => php_uname('s'),
        'php_memory_limit'  => ini_get('memory_limit'),
    ],
    'samples_raw'   => $responseTimes,
    'samples_used'  => count($trimmed),
    'avg_ms'        => round($mean, 2),
    'min_ms'        => round($min, 2),
    'max_ms'        => round($max, 2),
    'p95_ms'        => round($p95, 2),
    'std_dev_ms'    => round($stdDev, 2),
    'coeff_var_pct' => round($coeffVar, 2),
];

$filename = "scripts/benchmark-{$label}.json";
file_put_contents($filename, json_encode($results, JSON_PRETTY_PRINT));
echo "\nSaved: $filename\n";
echo "Average: {$results['avg_ms']} ms\n";
echo "P95:     {$results['p95_ms']} ms\n";
echo "StdDev:  {$results['std_dev_ms']} ms (CV: {$results['coeff_var_pct']}%)\n";
