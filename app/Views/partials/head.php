<?php
/**
 * @var string|null $title
 * @var bool|null $forceDarkTheme
 */
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-token-name" content="<?= csrf_token() ?>">
<title><?= $title ?? 'Catatan Keuangan' ?></title>
<!-- Google Fonts: Outfit -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Compiled Tailwind CSS -->
<link rel="stylesheet" href="<?= base_url('css/app.css') ?>">

<style>
    body {
        font-family: 'Outfit', sans-serif;
    }
    /* Hide scrollbar for Chrome, Safari and Opera */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    /* Smooth transition for navigation scroll */
    #mainNavLinks {
        scroll-behavior: smooth;
    }
</style>
<?= view('partials/theme_script', ['forceDarkTheme' => $forceDarkTheme ?? false]) ?>
