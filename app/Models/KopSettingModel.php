<?php

namespace App\Models;

use CodeIgniter\Model;

class KopSettingModel extends Model
{
    protected $table            = 'kop_settings';
    protected $primaryKey       = 'key';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['key', 'value'];

    /**
     * Get setting value by key with granular caching and flock() double-checked locking protection.
     *
     * @param string $key
     * @param mixed $default
     * @return string|null
     */
    public static function getSetting(string $key, $default = null)
    {
        $cacheKey = "kop_setting_{$key}";
        
        // 1. First fast cache check
        $value = cache($cacheKey);
        if ($value !== null) {
            return $value;
        }

        // 2. Cache miss - Apply OS-level flock() mutex lock to prevent thundering herd
        $lockFile = WRITEPATH . 'cache/settings.lock';
        
        // Ensure cache directory exists
        if (!is_dir(dirname($lockFile))) {
            mkdir(dirname($lockFile), 0777, true);
        }

        $fp = fopen($lockFile, 'c');
        if (!$fp) {
            // Safe fallback if lock file cannot be opened (query DB directly)
            $model = new self();
            $setting = $model->find($key);
            return $setting ? $setting['value'] : $default;
        }

        if (flock($fp, LOCK_EX)) {
            // Double-check cache inside the lock
            $value = cache($cacheKey);
            if ($value === null) {
                // Fetch from database
                $model = new self();
                $setting = $model->find($key);
                $value = $setting ? (string) $setting['value'] : (string) $default;

                // Cache the value for 1 hour (3600 seconds)
                cache()->save($cacheKey, $value, 3600);
            }
            flock($fp, LOCK_UN);
        }
        
        fclose($fp);
        return $value;
    }

    /**
     * Set setting value by key (upsert) and invalidate granular cache keys.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function setSetting(string $key, $value): bool
    {
        $db = \Config\Database::connect();
        $res = $db->table('kop_settings')->replace([
            'key'   => $key,
            'value' => (string) $value,
        ]);

        if ($res) {
            // Invalidate granular cache key
            cache()->delete("kop_setting_{$key}");
            
            // Also invalidate global settings cache just in case
            cache()->delete("kop_settings_cached_all");
            cache()->delete("kop_settings_all");
        }

        return (bool) $res;
    }
}
