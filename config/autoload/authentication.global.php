<?php

declare(strict_types=1);

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserRepositoryInterface;

return [
    'dependencies' => [
        'aliases' => [
            AuthenticationInterface::class => PhpSession::class,
            UserRepositoryInterface::class => \User\Service\MezzioUserRepository::class,
        ],
        'factories' => [
            \User\Service\MezzioUserRepository::class => function($container) {
                return new \User\Service\MezzioUserRepository(
                    $container->get(\User\Service\AuthenticationService::class)
                );
            },
        ],
    ],

    'authentication' => [
        'redirect' => '/user/login',
        'username' => 'credential',
        'password' => 'password',
    ],
];
