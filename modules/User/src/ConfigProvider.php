<?php

declare(strict_types=1);

namespace User;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Mezzio\Csrf\CsrfGuardInterface;
use Mezzio\Csrf\SessionCsrfGuard;
use User\Handler;
use User\Middleware;
use User\Service;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
            'authentication' => $this->getAuthenticationConfig(),
            'authorization' => $this->getAuthorizationConfig(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'factories' => [
                // Services
                Service\UserRepository::class => Service\UserRepositoryFactory::class,
                Service\AuthenticationService::class => Service\AuthenticationServiceFactory::class,
                
                // Handlers
                Handler\LoginHandler::class => Handler\LoginHandlerFactory::class,
                Handler\RegistrationHandler::class => Handler\RegistrationHandlerFactory::class,
                Handler\LogoutHandler::class => InvokableFactory::class,
                Handler\DashboardHandler::class => Handler\DashboardHandlerFactory::class,
                Handler\AdminHandler::class => Handler\AdminHandlerFactory::class,
                
                // Middleware
                Middleware\CsrfMiddleware::class => Middleware\CsrfMiddlewareFactory::class,
                Middleware\RequireLoginMiddleware::class => Middleware\RequireLoginMiddlewareFactory::class,
                Middleware\RequireRoleMiddleware::class => Middleware\RequireRoleMiddlewareFactory::class,
                
                // Authentication & Authorization
                AuthenticationInterface::class => Service\SimpleAuthenticationFactory::class,
                AuthorizationInterface::class => LaminasRbac::class,
                CsrfGuardInterface::class => SessionCsrfGuard::class,
            ],
            'aliases' => [
                'authentication' => AuthenticationInterface::class,
                'authorization' => AuthorizationInterface::class,
                'csrf' => CsrfGuardInterface::class,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'user' => [__DIR__ . '/../templates/user'],
            ],
        ];
    }

    public function getAuthenticationConfig(): array
    {
        return [
            'username' => 'credential',
            'password' => 'password',
            'redirect' => '/user/login',
        ];
    }

    public function getAuthorizationConfig(): array
    {
        return [
            'roles' => [
                'guest' => [],
                'user' => ['guest'],
                'admin' => ['user'],
            ],
            'permissions' => [
                'guest' => [
                    'user.login',
                    'user.register',
                ],
                'user' => [
                    'user.dashboard',
                    'user.profile',
                    'user.logout',
                ],
                'admin' => [
                    'user.admin',
                    'user.manage',
                ],
            ],
        ];
    }
}
