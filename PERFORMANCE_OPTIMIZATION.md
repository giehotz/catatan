# Performance Optimization

## Cache Strategy

### Mobile Detection (Session-based)

**Problem:** Every `view()` call instantiated `UserAgent` and called `isMobile()` on
every request, even though the result rarely changes.

**Solution:** Cache the UserAgent detection result in `$_SESSION['_mobile_cache']`.

```
Before (per request):
  Services::request()->getUserAgent()  → instantiate UserAgent
  $agent->isMobile()                   → parse User-Agent header

After (per session):
  $_SESSION['_mobile_cache']['is_mobile']  → read cached bool
```

**Performance:** ~0.1-0.5ms saved per `view()` call by avoiding UserAgent
class instantiation.

**Invalidation triggers:**
- Session belum ter-set → hitung ulang
- `$_GET['preview']` berbeda dari `preview_mode` tersimpan → hitung ulang
- `?preview=clear` → reset cache

**Fallback:** Static array untuk CLI (no session available).

**Validation:** Hanya `'mobile'` / `'desktop'` / `'clear'` yang allowed untuk
preview param.

---

### View Path (Static Array per-request)

**Problem:** `file_exists($viewPath)` dipanggil berulang kali dalam 1 request
untuk view path yang sama.

**Solution:** Static cache array `$viewPathCache` dengan max 1000 entries.

```
Before:
  file_exists($viewPath)  // called every time

After:
  $viewPathCache[$viewPath] = file_exists($viewPath)  // cached per-request
```

**Lifecycle:** Cache valid only per-request (static reset otomatis di akhir request).
TTL tidak diperlukan. Size limit 1000 mencegah memory leak.

---

### Preload Regex (Compiled Regex)

**Problem:** Nested `str_contains` loop iterating over every exclude pattern
for every file in preload iteration.

**Before:**
```php
foreach ($phpFiles as $file) {
    foreach ($path['exclude'] as $exclude) {
        if (str_contains($file[0], $exclude)) {
            continue 2;
        }
    }
    require_once $file[0];
}
```

**After:**
```php
// Build compiled regex once
$excludeRegex = '#' . implode('|', array_map('preg_quote', $excludePatterns)) . '#i';

foreach ($phpFiles as $file) {
    if (preg_match($excludeRegex, $file[0])) {
        continue;
    }
    require_once $file[0];
}
```

**Cross-platform:** Path separators dinormalisasi ke `/` untuk Unix/Windows compatibility.

**Regex generation:** Programmatic dari array `$paths['exclude']`, bukan hardcode.

---

## Why Each Optimization Was Chosen

| Optimization | Rationale | Trade-off |
|---|---|---|
| Mobile session cache | Session sudah tersedia, no extra infra | Cache bisa stale sampai session expire |
| View path cache | Eliminate filesystem call per request | Memory (max 1000 entries) |
| Preload regex | Single pass vs nested loop | Regex compilation overhead (one-time) |

## Statistical Criteria

Setiap optimasi di-commit hanya jika:

1. **Improvement ≥ 5%** dibanding baseline
2. **Coefficient of Variation (CV) < 10%** (hasil stabil)
3. **P95 juga menunjukkan improvement** (bukan cuma average)

## Maintenance Guide

### How to Add New Optimization

1. Record baseline metrics: `php scripts/benchmark.php baseline`
2. Implement optimization
3. Record optimized metrics: `php scripts/benchmark.php optimized`
4. Compare: jika ≥5% improvement dengan CV < 10%, commit
5. Jika tidak, skip — jangan premature optimization

### How to Extend Mobile Cache

```php
// Add new cache key in session
$_SESSION['_mobile_cache']['new_feature'] = $value;

// Invalidate by adding to preview check
$previewParam = $_GET['preview'] ?? null;
if ($previewParam === 'new-reset') {
    unset($_SESSION['_mobile_cache']['new_feature']);
}
```

### How to Update Preload Exclusions

Edit the `$paths['exclude']` array in `preload.php`. The regex is generated
automatically — no need to update hardcoded patterns.
