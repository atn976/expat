<?php

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => 'localhost',
            'name' => 'expat',
            'user' => 'root',
            'pass' => '',
            'port' => 3306,
            'charset' => 'utf8mb4',
        ],
    ],
    'version_order' => 'creation'
];
