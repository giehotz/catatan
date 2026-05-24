<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

use Config\Services;

if (!function_exists('view')) {
    /**
     * Cache for file_exists() checks on view paths.
     * Valid per-request execution only. Automatically resets at end of request.
     *
     * @var array<string, bool>
     */
    static $viewPathCache = [];

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     */
    function view(string $name, array $data = [], array $options = []): string
    {
        static $isMobilePhone = null;

        if ($isMobilePhone === null) {
            $sessionMode = null;
            $previewParam = $_GET['preview'] ?? null;

            // Validate preview param — hanya 'mobile' atau 'desktop'
            if ($previewParam && !in_array($previewParam, ['mobile', 'desktop', 'clear'], true)) {
                $previewParam = null;
            }

            // Coba akses session jika tersedia dan bukan di CLI
            if (!is_cli() && session_status() !== PHP_SESSION_DISABLED) {
                try {
                    $session = Services::session();
                    $hasSession = true;

                    if ($previewParam !== null) {
                        if ($previewParam === 'mobile' || $previewParam === 'desktop') {
                            $session->set('preview_mode', $previewParam);
                        } elseif ($previewParam === 'clear') {
                            $session->remove('preview_mode');
                        }
                    }
                    $sessionMode = $session->get('preview_mode');
                } catch (\Throwable $e) {
                    $hasSession = false;
                }
            } else {
                $hasSession = false;
            }

            // Jika session tidak tersedia/belum diset, baca dari query param langsung
            if ($sessionMode === null && $previewParam !== null) {
                $sessionMode = $previewParam;
            }

            if ($sessionMode === 'desktop') {
                $isMobilePhone = false;
            } elseif ($sessionMode === 'mobile') {
                $isMobilePhone = true;
            } else {
                // Check session cache for UserAgent detection
                $cached = null;
                if ($hasSession && isset($_SESSION['_mobile_cache'])) {
                    $cached = $_SESSION['_mobile_cache'];
                    // Invalidate jika preview param berubah
                    if ($cached['preview_mode'] !== $sessionMode) {
                        $cached = null;
                        unset($_SESSION['_mobile_cache']);
                    }
                }

                if ($cached !== null && isset($cached['is_mobile'])) {
                    $isMobilePhone = $cached['is_mobile'];
                } else {
                    $agent = Services::request()->getUserAgent();
                    $isMobilePhone = $agent && $agent->isMobile();

                    if ($hasSession) {
                        $_SESSION['_mobile_cache'] = [
                            'is_mobile'    => $isMobilePhone,
                            'preview_mode' => $sessionMode,
                            'timestamp'    => time(),
                        ];
                    }
                }
            }
        }

        if ($isMobilePhone) {
            $mobileView = 'mobile/' . $name;
            $viewPath = APPPATH . 'Views/' . $mobileView . '.php';

            if (!isset($viewPathCache[$viewPath])) {
                $viewPathCache[$viewPath] = file_exists($viewPath);
            }

            if ($viewPathCache[$viewPath]) {
                $name = $mobileView;
            }
        }

        $renderer = Services::renderer();
        $saveData = $options['saveData'] ?? true;
        unset($options['saveData']);

        return $renderer->setData($data, 'raw')
            ->render($name, $options, $saveData);
    }
}
