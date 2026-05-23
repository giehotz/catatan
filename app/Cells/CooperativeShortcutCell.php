<?php

namespace App\Cells;

use App\Models\KopAnggotaModel;

class CooperativeShortcutCell
{
    public function render(): string
    {
        if (!auth()->loggedIn()) {
            return '';
        }
        
        $isActive = false;
        
        try {
            $userId = auth()->id();
            $cacheKey = "coop_member_active_{$userId}";
            
            // Caching Global O(1) agar admin dapat menginvalidasinya secara instan
            $cachedStatus = cache($cacheKey);
            if ($cachedStatus !== null) {
                $isActive = (bool) $cachedStatus;
            } else {
                $anggotaModel = new KopAnggotaModel();
                $member = $anggotaModel->where('user_id', $userId)
                                       ->where('status_keaktifan', 'aktif')
                                       ->first();
                $isActive = !empty($member);
                cache()->save($cacheKey, $isActive ? 1 : 0, 300); // Caching selama 5 menit
            }
        } catch (\Throwable $e) {
            // Catat ke log secara anggun jika DB crash
            log_message('error', 'CooperativeShortcutCell Error: ' . $e->getMessage());
            return ''; // Kembalikan string kosong sebagai fallback aman jika database bermasalah
        }
        
        $url = $isActive ? base_url('cooperative') : base_url('cooperative/join');
        $label = $isActive ? 'Portal KSP' : 'Gabung KSP';
        $styleClass = $isActive 
            ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/25 hover:bg-emerald-500/20 shadow-emerald-500/5' 
            : 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/25 hover:bg-indigo-500/20 shadow-indigo-500/5';
            
        // Render dengan XSS sanitasi terproteksi penuh
        return '
        <a href="' . (string) esc($url, 'attr') . '" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold transition-all shadow-sm ' . (string) esc($styleClass, 'attr') . '">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            <span class="hidden sm:inline">' . (string) esc($label, 'html') . '</span>
        </a>';
    }
}
