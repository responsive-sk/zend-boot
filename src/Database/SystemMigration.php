<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * System Database Migration
 *
 * Creates system.db with core system modules:
 * - Cache management
 * - System logs
 * - Template cache
 * - Configuration cache
 */
class SystemMigration
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function migrate(): void
    {
        $this->createCacheTable();
        $this->createSystemLogsTable();
        $this->createTemplateCacheTable();
        $this->createConfigCacheTable();
        $this->createSystemSettingsTable();

        echo "✅ System database migration completed\n";
    }

    private function createCacheTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS cache (
                id TEXT PRIMARY KEY,
                namespace TEXT NOT NULL,
                data TEXT NOT NULL,
                expires_at INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $this->pdo->exec($sql);

        // Create indexes separately
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_cache_namespace ON cache(namespace)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_cache_expires ON cache(expires_at)');
        echo "  📦 Cache table created\n";
    }

    private function createSystemLogsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS system_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                level TEXT NOT NULL,
                message TEXT NOT NULL,
                context TEXT,
                module TEXT,
                trace_id TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $this->pdo->exec($sql);

        // Create indexes separately
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_system_logs_level ON system_logs(level)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_system_logs_module ON system_logs(module)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_system_logs_created ON system_logs(created_at)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_system_logs_trace ON system_logs(trace_id)');

        echo "  📝 System logs table created\n";
    }

    private function createTemplateCacheTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS template_cache (
                id TEXT PRIMARY KEY,
                template_name TEXT NOT NULL,
                compiled_content TEXT NOT NULL,
                source_hash TEXT NOT NULL,
                expires_at INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $this->pdo->exec($sql);
        echo "  🎨 Template cache table created\n";
    }

    private function createConfigCacheTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS config_cache (
                id TEXT PRIMARY KEY,
                config_key TEXT NOT NULL UNIQUE,
                config_value TEXT NOT NULL,
                environment TEXT NOT NULL DEFAULT 'production',
                expires_at INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $this->pdo->exec($sql);
        echo "  ⚙️ Config cache table created\n";
    }

    private function createSystemSettingsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS system_settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT NOT NULL UNIQUE,
                setting_value TEXT NOT NULL,
                setting_type TEXT NOT NULL DEFAULT 'string',
                description TEXT,
                is_public BOOLEAN DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $this->pdo->exec($sql);

        // Insert default system settings
        $this->insertDefaultSettings();
        echo "  🔧 System settings table created\n";
    }

    private function insertDefaultSettings(): void
    {
        $defaultSettings = [
            ['app_name', 'Mezzio User App', 'string', 'Application name', 1],
            ['app_version', '1.0.0', 'string', 'Application version', 1],
            ['maintenance_mode', '0', 'boolean', 'Maintenance mode status', 0],
            ['cache_enabled', '1', 'boolean', 'Cache system enabled', 0],
            ['log_level', 'info', 'string', 'System log level', 0],
            ['session_timeout', '3600', 'integer', 'Session timeout in seconds', 0],
        ];

        $stmt = $this->pdo->prepare("
            INSERT OR IGNORE INTO system_settings 
            (setting_key, setting_value, setting_type, description, is_public) 
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
    }
}
