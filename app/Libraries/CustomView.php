<?php

namespace App\Libraries;

use CodeIgniter\View\View;
use Config\Services;

class CustomView extends View
{
    /**
     * Overrides extend() to dynamically swap the desktop base layout
     * with the premium mobile base layout when a mobile device or preview is active.
     */
    public function extend(string $layout)
    {
        static $isMobilePhone = null;

        if ($isMobilePhone === null) {
            $sessionMode = null;
            if (!is_cli() && session_status() !== PHP_SESSION_DISABLED) {
                try {
                    $session = Services::session();
                    $sessionMode = $session->get('preview_mode');
                } catch (\Throwable $e) {}
            }

            if ($sessionMode === null && isset($_GET['preview'])) {
                $sessionMode = $_GET['preview'];
            }

            if ($sessionMode === 'desktop') {
                $isMobilePhone = false;
            } elseif ($sessionMode === 'mobile') {
                $isMobilePhone = true;
            } else {
                $agent = Services::request()->getUserAgent();
                $isMobilePhone = $agent && $agent->isMobile();
            }
        }

        // Swap base layout to mobile base layout dynamically
        if ($isMobilePhone && $layout === 'layouts/base') {
            log_message('debug', 'CustomView::extend swapping layouts/base to layouts/mobile_base');
            $layout = 'layouts/mobile_base';
        }

        parent::extend($layout);
    }
}
