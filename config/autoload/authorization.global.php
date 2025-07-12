<?php

declare(strict_types=1);

use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Laminas\Permissions\Rbac\Rbac;

return [
    'dependencies' => [
        'factories' => [
            AuthorizationInterface::class => function ($container) {
                $rbac = new Rbac();
                
                // Define roles
                $rbac->addRole('user');
                $rbac->addRole('editor', ['user']);
                $rbac->addRole('mark', ['editor']);
                $rbac->addRole('supermark', ['mark']);
                
                // Define permissions
                $rbac->getRole('user')->addPermission('view.public');
                $rbac->getRole('editor')->addPermission('edit.content');
                $rbac->getRole('mark')->addPermission('manage.orbit');
                $rbac->getRole('mark')->addPermission('access.mark');
                $rbac->getRole('supermark')->addPermission('manage.users');
                $rbac->getRole('supermark')->addPermission('system.admin');
                
                return new LaminasRbac($rbac);
            },
        ],
    ],
    
    'mezzio-authorization-rbac' => [
        'roles' => [
            'user' => [],
            'editor' => ['user'],
            'mark' => ['editor'],
            'supermark' => ['mark'],
        ],
        'permissions' => [
            'user' => [
                'view.public',
            ],
            'editor' => [
                'edit.content',
            ],
            'mark' => [
                'manage.orbit',
                'access.mark',
            ],
            'supermark' => [
                'manage.users',
                'system.admin',
            ],
        ],
    ],
];
