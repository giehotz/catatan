<?php
/**
 * @var bool|null $forceDarkTheme
 */
?>
<script>
    (function() {
        const forceDark = <?= isset($forceDarkTheme) && $forceDarkTheme ? 'true' : 'false' ?>;
        const serverTheme = '<?= (auth()->loggedIn() && isset(auth()->user()->theme_preference)) ? esc((string) auth()->user()->theme_preference) : '' ?>';
        const saved = forceDark ? 'dark' : (serverTheme || localStorage.getItem('theme') || 'system');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        let activeTheme = 'dark';
        if (forceDark) {
            activeTheme = 'dark';
        } else if (saved === 'light' || saved === 'dark') {
            activeTheme = saved;
        } else {
            activeTheme = prefersDark ? 'dark' : 'light';
        }
        
        document.documentElement.className = 'h-full theme-' + activeTheme;
        
        let meta = document.querySelector('meta[name="theme-color"]');
        if (!meta) {
            meta = document.createElement('meta');
            meta.name = 'theme-color';
            document.head.appendChild(meta);
        }
        meta.content = activeTheme === 'dark' ? '#020617' : '#f8fafc';
    })();
</script>
