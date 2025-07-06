<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'factories' => [
            // Register SessionPersistenceInterface manually (without Session Ext)
            \Mezzio\Session\SessionPersistenceInterface::class => function ($container) {
                return new \App\Session\SimpleSessionPersistence();
            },
        ],
    ],

    'session' => [
        'cookie_name' => 'mezzio',
        'cookie_domain' => '',
        'cookie_path' => '/',
        'cookie_secure' => false,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cache_expire' => 180,
        'cache_limiter' => 'nocache',
        'persistent' => true,
        'ini_settings' => [
            'session.gc_maxlifetime' => 7200,
            'session.gc_probability' => 1,
            'session.gc_divisor' => 100,
            'session.cookie_lifetime' => 0,
            'session.use_cookies' => 1,
            'session.use_only_cookies' => 1,
        ],
    ],
];
