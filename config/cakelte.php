<?php
use Cake\ORM\TableRegistry;

/**
 * Reads the branding parameters from the `config` table.
 *
 * This file is loaded by CakeLteHelper while the View is being built, which
 * also happens when rendering an error page. If the config table is missing
 * rows (fresh install before seeding, or a broken database) it must degrade
 * gracefully instead of emitting warnings and masking the real error.
 */
$readConfig = function (string $param, string $default = ''): string {
    try {
        $row = TableRegistry::getTableLocator()->get('Config')
            ->find()
            ->where(['param' => $param])
            ->first();
    } catch (\Throwable $e) {
        return $default;
    }

    return $row->value ?? $default;
};

$kingdomNumber = $readConfig('kingdom_number');
$clanAcronym = $readConfig('clan_acronym');
$clanName = $readConfig('clan_name', 'ChestCounter');

return [
    'CakeLte' => [
        'app-name' => '<b>' . $kingdomNumber . ' </b> ' . $clanAcronym . ' <b>' . $clanName . '</b>',
        'app-logo' => 'CakeLte.logo.png',
        'small-text' => true,
        'dark-mode' => false,
        'layout-boxed' => false,

        'theme' => [
            'folder' => 'CakeLte',
            'skin' => 'blue',
        ],
        'footer' => [
            'left' => 'ChestCounter',
            'right' => 'Versão 0.3'
        ],
        'sidebar' => [
            'enable' => false,
            'collapse' => false
        ],
        'navbar' => [
            'enable' => true
        ]
    ]
];
