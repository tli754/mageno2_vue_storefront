<?php
return [
    'backend' => [
        'frontName' => 'mage'
    ],
    'crypt' => [
        'key' => '45ae0b65bc278684f32d42f1e4bf4c35'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'mysql',
                'dbname' => 'magento2',
                'username' => 'root',
                'password' => 'Go@w@y2020-root',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ],
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;'
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'developer',
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'google_product' => 1,
        'full_page' => 0,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => 'redis-cache',
                    'port' => '6379'
                ],
                'id_prefix' => '38c_'
            ],
            'page_cache' => [
                'backend' => 'Cm_Cache_Backend_Redis',
                'backend_options' => [
                    'server' => 'redis-cache',
                    'port' => '6379',
                    'database' => '1',
                    'compress_data' => '0'
                ],
                'id_prefix' => '38c_'
            ]
        ]
    ],
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => 'redis-session',
            'port' => '6379',
            'password' => '',
            'timeout' => '2.5',
            'persistent_identifier' => '',
            'database' => '1',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '1',
            'max_concurrency' => '6',
            'break_after_frontend' => '5',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '0',
            'min_lifetime' => '60',
            'max_lifetime' => '2592000'
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => ''
        ]
    ],
    'downloadable_domains' => [
        'www.magento2.local'
    ],
    'queue' => [
        'amqp' => [
            'host' => 'rabbitmq',
            'port' => 5672,
            'user' => 'user',
            'password' => 'pass',
            'virtualhost' => '/'
        ],
        'consumers_wait_for_messages' => 1
    ],
    'cron_consumers_runner' => [
        'cron_run' => false,
        'max_messages' => 20000,
        'consumers' => [
            'consumer1',
            'consumer2'
        ]
    ],
    'install' => [
        'date' => 'Tue, 18 Feb 2020 10:38:46 +0000'
    ]
];
