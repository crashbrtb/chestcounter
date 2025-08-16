<?php
use Cake\ORM\TableRegistry;

$configTable = TableRegistry::getTableLocator()->get('Config');
$kingdomNumber = $configTable->find()->where(['param' => 'kingdom_number'])->first()->value;
$clanAcronym = $configTable->find()->where(['param' => 'clan_acronym'])->first()->value;
$clanName = $configTable->find()->where(['param' => 'clan_name'])->first()->value;

return [
    'CakeLte' => [
        'app-name' => '<b>' . $kingdomNumber . ' </b> ' . $clanAcronym . ' <b>' . $clanName . '</b>',
        'app-logo' => 'CakeLte.dkw.png',
        'small-text' => true,
        'dark-mode' => false,
        'layout-boxed' => false,
        
        'theme' => [
            'folder' => 'CakeLte',
            'skin' => 'blue',
        ],
        'footer' => [
            'left' => 'ChestCounter',
            'right' => 'Versão 1.0'
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