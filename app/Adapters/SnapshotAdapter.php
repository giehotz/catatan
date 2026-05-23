<?php

namespace App\Adapters;

class SnapshotAdapter
{
    /**
     * Upgrades a snapshot data array dynamically based on its internal schema_version.
     *
     * @param array $data The raw parsed snapshot array.
     * @param string $type The snapshot type: 'kop', 'signer', 'format'.
     * @param int $targetVersion The target schema version (default is 1).
     * @return array The upgraded snapshot array.
     */
    public static function upgrade(array $data, string $type, int $targetVersion = 1): array
    {
        $currentVersion = $data['schema_version'] ?? 1;

        if ($currentVersion >= $targetVersion) {
            return $data;
        }

        // Perform sequential version upgrades on-the-fly
        for ($v = $currentVersion; $v < $targetVersion; $v++) {
            $method = "upgrade" . ucfirst($type) . "To" . ($v + 1);
            if (method_exists(self::class, $method)) {
                $data = self::$method($data);
                $data['schema_version'] = $v + 1;
            }
        }

        return $data;
    }

    /**
     * Upgrade KOP schema from Version 1 to Version 2 (Placeholder example).
     */
    protected static function upgradeKopTo2(array $data): array
    {
        $data['district'] = $data['district'] ?? 'DKI Jakarta';
        return $data;
    }

    /**
     * Upgrade Signer schema from Version 1 to Version 2 (Placeholder example).
     */
    protected static function upgradeSignerTo2(array $data): array
    {
        $data['signer_priority'] = $data['signer_priority'] ?? 0;
        return $data;
    }

    /**
     * Upgrade Format schema from Version 1 to Version 2 (Placeholder example).
     */
    protected static function upgradeFormatTo2(array $data): array
    {
        $data['branch_code'] = $data['branch_code'] ?? 'PST';
        return $data;
    }
}
