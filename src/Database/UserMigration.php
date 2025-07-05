<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * User Database Migration
 *
 * HDM Boot Protocol Compliant - User System Database
 * Creates user.db with user management tables
 */
class UserMigration
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function migrate(): void
    {
        $this->createUsersTable();
        $this->createUserSessionsTable();
        $this->createUserPermissionsTable();
        $this->seedDefaultUsers();

        echo "✅ User database migration completed\n";
    }

    private function createUsersTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                roles TEXT NOT NULL DEFAULT '[]',
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login_at DATETIME NULL,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";

        $this->pdo->exec($sql);

        // Create indexes separately
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_active ON users(is_active)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_users_created ON users(created_at)');
        echo "  👤 Users table created\n";
    }

    private function createUserSessionsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS user_sessions (
                id TEXT PRIMARY KEY,
                user_id INTEGER NOT NULL,
                ip_address TEXT,
                user_agent TEXT,
                last_activity INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";

        $this->pdo->exec($sql);

        // Create indexes separately
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_user_sessions_user_id ON user_sessions(user_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_user_sessions_activity ON user_sessions(last_activity)');
        echo "  🔐 User sessions table created\n";
    }

    private function createUserPermissionsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS user_permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                permission TEXT NOT NULL,
                granted_by INTEGER,
                granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NULL,

                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL
            )
        ";

        $this->pdo->exec($sql);

        // Create indexes separately
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_user_permissions_user_id ON user_permissions(user_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_user_permissions_permission ON user_permissions(permission)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_user_permissions_expires ON user_permissions(expires_at)');
        echo "  🔑 User permissions table created\n";
    }

    private function seedDefaultUsers(): void
    {
        // Check if users already exist
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users');
        $stmt->execute();
        $userCount = $stmt->fetchColumn();

        if ($userCount == 0) {
            echo "  🌱 Seeding default users...\n";

            // Create default users (HDM Boot Protocol compliant)
            $this->createDefaultUser(
                'admin',
                'admin@example.com',
                'admin123',
                ['admin', 'user']
            );

            $this->createDefaultUser(
                'user',
                'user@example.com',
                'user123',
                ['user']
            );

            $this->createDefaultUser(
                'mark',
                'mark@example.com',
                'mark123',
                ['mark', 'user']
            );

            echo "  ✅ Default users created\n";
        } else {
            echo "  ℹ️ Users already exist, skipping seeding\n";
        }
    }

    /**
     * @param array<string> $roles
     */
    private function createDefaultUser(string $username, string $email, string $password, array $roles): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (username, email, password_hash, roles, created_at, updated_at)
            VALUES (?, ?, ?, ?, datetime("now"), datetime("now"))
        ');

        $stmt->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            json_encode($roles)
        ]);

        echo "    👤 Created user: {$username} (" . implode(', ', $roles) . ")\n";
    }
}
