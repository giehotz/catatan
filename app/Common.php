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
     * Grabs the current RendererInterface and renders the view.
     * Overridden to serve mobile-specific views automatically with dynamic fallbacks
     * and session-persistent preview options.
     *
     * PRINSIP: Hanya menukar nama berkas view yang dimuat. Data ($data) dan konfigurasi ($options)
     * tetap dikirimkan secara utuh ke presenter.
     */
    function view(string $name, array $data = [], array $options = []): string
    {
        static $isMobilePhone = null;

        if ($isMobilePhone === null) {
            $sessionMode = null;
            
            // Coba akses session jika tersedia dan bukan di CLI
            if (!is_cli() && session_status() !== PHP_SESSION_DISABLED) {
                try {
                    $session = Services::session();
                    if (isset($_GET['preview'])) {
                        $p = $_GET['preview'];
                        if ($p === 'mobile' || $p === 'desktop') {
                            $session->set('preview_mode', $p);
                        } elseif ($p === 'clear') {
                            $session->remove('preview_mode');
                        }
                    }
                    $sessionMode = $session->get('preview_mode');
                } catch (\Throwable $e) {
                    // Safe fallback
                }
            }

            // Jika session tidak tersedia/belum diset, baca dari query param langsung
            if ($sessionMode === null && isset($_GET['preview'])) {
                $sessionMode = $_GET['preview'];
            }

            if ($sessionMode === 'desktop') {
                $isMobilePhone = false;
            } elseif ($sessionMode === 'mobile') {
                $isMobilePhone = true;
            } else {
                $agent = Services::request()->getUserAgent();
                // Hanya layani tampilan mobile untuk smartphone jika agent terdeteksi
                $isMobilePhone = $agent && $agent->isMobile();
            }
        }

        if ($isMobilePhone) {
            $mobileView = 'mobile/' . $name;
            $viewPath = APPPATH . 'Views/' . $mobileView . '.php';

            if (file_exists($viewPath)) {
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
