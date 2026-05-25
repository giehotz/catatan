<?php

namespace App\Models;

use CodeIgniter\Model;

class KopShuConfiguration extends Model
{
    protected $table            = 'kop_shu_configuration';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['key', 'value', 'description', 'is_editable'];

    protected $useTimestamps = false;

    public static function getConfig(string $key, $default = null)
    {
        $model = new self();
        $config = $model->where('key', $key)->first();
        return $config ? $config['value'] : $default;
    }

    public static function setConfig(string $key, $value): bool
    {
        $model = new self();
        $existing = $model->where('key', $key)->first();

        if ($existing) {
            return $model->update($existing['id'], ['value' => $value]);
        }

        return $model->insert(['key' => $key, 'value' => $value]) !== false;
    }
}
