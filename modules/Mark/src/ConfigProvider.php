<?php

declare(strict_types=1);

namespace Mark;

/**
 * HDM Boot Protocol - Mark Module Configuration
 * 
 * Configuration for mark users system (mark, editor, supermark roles)
 * Separate from user module for security and organization
 */
class ConfigProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
            'mark_authentication' => $this->getMarkAuthenticationConfig(),
            'mark_authorization' => $this->getMarkAuthorizationConfig(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                // Mark Authentication & Authorization
                Middleware\MarkAuthenticationMiddleware::class => Middleware\MarkAuthenticationMiddlewareFactory::class,
                Middleware\SupermarkAuthorizationMiddleware::class => Middleware\SupermarkAuthorizationMiddlewareFactory::class,
                
                // Mark Handlers - Dashboard & System
                Handler\DashboardHandler::class => Handler\DashboardHandlerFactory::class,
                Handler\HealthHandler::class => Handler\HealthHandlerFactory::class,
                // TODO: Implement these handlers
                // Handler\LogsHandler::class => Handler\LogsHandlerFactory::class,
                // Handler\DatabaseHandler::class => Handler\DatabaseHandlerFactory::class,
                // Handler\SettingsHandler::class => Handler\SettingsHandlerFactory::class,
                
                // TODO: Implement these handlers
                // Mark Handlers - User Management
                // Handler\UserManagementHandler::class => Handler\UserManagementHandlerFactory::class,
                // Handler\UserEditHandler::class => Handler\UserEditHandlerFactory::class,
                // Handler\UserDeleteHandler::class => Handler\UserDeleteHandlerFactory::class,

                // Mark Handlers - Mark Management
                // Handler\MarkManagementHandler::class => Handler\MarkManagementHandlerFactory::class,
                // Handler\MarkCreateHandler::class => Handler\MarkCreateHandlerFactory::class,
                // Handler\MarkEditHandler::class => Handler\MarkEditHandlerFactory::class,
                // Handler\MarkDeleteHandler::class => Handler\MarkDeleteHandlerFactory::class,

                // Mark Handlers - System Management
                // Handler\CacheHandler::class => Handler\CacheHandlerFactory::class,
                // Handler\CacheClearHandler::class => Handler\CacheClearHandlerFactory::class,
                // Handler\BackupHandler::class => Handler\BackupHandlerFactory::class,
                // Handler\BackupCreateHandler::class => Handler\BackupCreateHandlerFactory::class,
                
                // Mark Handlers - Authentication
                Handler\LoginHandler::class => Handler\LoginHandlerFactory::class,
                Handler\LogoutHandler::class => Handler\LogoutHandlerFactory::class,
                
                // TODO: Implement API handlers
                // Mark API Handlers
                // Handler\Api\StatsHandler::class => Handler\Api\StatsHandlerFactory::class,
                // Handler\Api\HealthHandler::class => Handler\Api\HealthHandlerFactory::class,
                
                // Mark Services
                Service\MarkUserRepository::class => Service\MarkUserRepositoryFactory::class,
                Service\SystemStatsService::class => Service\SystemStatsServiceFactory::class,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'mark' => [__DIR__ . '/../templates/mark'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getMarkAuthenticationConfig(): array
    {
        return [
            'redirect' => '/mark/login',
            'session_key' => 'mark_user',
            'roles' => [
                'mark' => 'Basic mark user',
                'editor' => 'Content editor mark user', 
                'supermark' => 'Super administrator mark user',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getMarkAuthorizationConfig(): array
    {
        return [
            'role_hierarchy' => [
                'supermark' => ['mark', 'editor', 'supermark'],
                'editor' => ['mark', 'editor'],
                'mark' => ['mark'],
            ],
            'permissions' => [
                // Dashboard access
                'mark.dashboard' => ['mark', 'editor', 'supermark'],
                'mark.health' => ['mark', 'editor', 'supermark'],
                'mark.logs' => ['mark', 'editor', 'supermark'],
                
                // User management
                'mark.users.view' => ['mark', 'editor', 'supermark'],
                'mark.users.edit' => ['editor', 'supermark'],
                'mark.users.delete' => ['supermark'],
                
                // Mark management
                'mark.marks.view' => ['mark', 'editor', 'supermark'],
                'mark.marks.create' => ['mark', 'editor', 'supermark'],
                'mark.marks.edit' => ['mark', 'editor', 'supermark'],
                'mark.marks.delete' => ['editor', 'supermark'],
                
                // System management
                'mark.database' => ['supermark'],
                'mark.settings' => ['supermark'],
                'mark.backups' => ['supermark'],
                'mark.cache' => ['mark', 'editor', 'supermark'],
                
                // API access
                'api.mark.stats' => ['mark', 'editor', 'supermark'],
                'api.mark.health' => ['mark', 'editor', 'supermark'],
            ],
        ];
    }
}
