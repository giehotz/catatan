<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use CodeIgniter\Boot;
use Config\Paths;

/*
 *---------------------------------------------------------------
 * Sample file for Preloading
 *---------------------------------------------------------------
 * See https://www.php.net/manual/en/opcache.preloading.php
 *
 * How to Use:
 *   0. Copy this file to your project root folder.
 *   1. Set the $paths property of the preload class below.
 *   2. Set opcache.preload in php.ini.
 *     php.ini:
 *     opcache.preload=/path/to/preload.php
 */

// Load the paths config file
require __DIR__ . '/app/Config/Paths.php';

// Path to the front controller
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

class preload
{
    /**
     * @var array Paths to preload.
     */
    private array $paths = [
        [
            'include' => __DIR__ . '/vendor/codeigniter4/framework/system', // Change this path if using manual installation
            'exclude' => [
                // Not needed if you don't use them.
                '/system/Database/OCI8/',
                '/system/Database/Postgre/',
                '/system/Database/SQLite3/',
                '/system/Database/SQLSRV/',
                // Not needed for web apps.
                '/system/Database/Seeder.php',
                '/system/Test/',
                '/system/CLI/',
                '/system/Commands/',
                '/system/Publisher/',
                '/system/ComposerScripts.php',
                // Not Class/Function files.
                '/system/Config/Routes.php',
                '/system/Language/',
                '/system/bootstrap.php',
                '/system/util_bootstrap.php',
                '/system/rewrite.php',
                '/Views/',
                // Errors occur.
                '/system/ThirdParty/',
            ],
        ],
    ];

    public function __construct()
    {
        $this->loadAutoloader();
    }

    private function loadAutoloader(): void
    {
        $paths = new Paths();
        require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';

        Boot::preload($paths);
    }

    /**
     * Build compiled exclusion regex from exclude path patterns.
     * Handles cross-platform path separators (Unix / vs Windows \).
     */
    private function buildExcludeRegex(array $excludePatterns): string
    {
        $normalized = array_map(function (string $p): string {
            return str_replace('\\', '/', $p);
        }, $excludePatterns);

        $escaped = array_map(function (string $p): string {
            return preg_quote($p, '#');
        }, $normalized);

        return '#' . implode('|', $escaped) . '#i';
    }

    /**
     * Load PHP files.
     */
    public function load(): void
    {
        foreach ($this->paths as $path) {
            $directory = new RecursiveDirectoryIterator($path['include']);
            $fullTree  = new RecursiveIteratorIterator($directory);
            $phpFiles  = new RegexIterator(
                $fullTree,
                '/.+((?<!Test)+\.php$)/i',
                RecursiveRegexIterator::GET_MATCH,
            );

            // Build compiled regex once — replaces nested str_contains loop
            $excludeRegex = $this->buildExcludeRegex($path['exclude']);

            foreach ($phpFiles as $key => $file) {
                // Normalize path separator for cross-platform matching
                $filePath = str_replace('\\', '/', $file[0]);

                if (preg_match($excludeRegex, $filePath)) {
                    continue;
                }

                require_once $file[0];
                // Uncomment only for debugging (to inspect which files are included).
                // Never use this in production - preload scripts must not generate output.
                // echo 'Loaded: ' . $file[0] . "\n";
            }
        }
    }
}

(new preload())->load();
