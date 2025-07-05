<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

class MigrationService
{
    public function __construct(
        private PDO $userPdo,
        private PDO $markPdo,
        private PDO $systemPdo
    ) {
    }

    public function migrate(): void
    {
        echo "🚀 Starting database migrations...\n\n";

        $this->createUserTables();
        $this->createMarkTables();
        $this->createSystemTables();
        $this->seedDefaultData();

        echo "\n✅ All migrations completed successfully!\n";
    }

    private function createUserTables(): void
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

        $this->userPdo->exec($sql);

        // Create indexes
        $this->userPdo->exec('CREATE INDEX IF NOT EXISTS idx_users_username ON users(username)');
        $this->userPdo->exec('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
        $this->userPdo->exec('CREATE INDEX IF NOT EXISTS idx_users_active ON users(is_active)');
    }

    private function createMarkTables(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS marks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                category VARCHAR(100),
                priority INTEGER DEFAULT 1,
                status VARCHAR(50) DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";

        $this->markPdo->exec($sql);

        // Create indexes
        $this->markPdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_user_id ON marks(user_id)');
        $this->markPdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_category ON marks(category)');
        $this->markPdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_status ON marks(status)');
        $this->markPdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_priority ON marks(priority)');

        echo "✅ Mark tables created\n";
    }

    private function createSystemTables(): void
    {
        echo "📦 Creating system tables...\n";

        $migration = new SystemMigration($this->systemPdo);
        $migration->migrate();
    }

    private function seedDefaultData(): void
    {
        // Check if users already exist
        $stmt = $this->userPdo->prepare('SELECT COUNT(*) FROM users');
        $stmt->execute();
        $userCount = $stmt->fetchColumn();

        if ($userCount == 0) {
            // Create default users
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
        }
    }

    /**
     * @param array<string> $roles
     */
    private function createDefaultUser(string $username, string $email, string $password, array $roles): void
    {
        $sql = "
            INSERT INTO users (username, email, password_hash, roles, is_active)
            VALUES (?, ?, ?, ?, 1)
        ";

        $stmt = $this->userPdo->prepare($sql);
        $stmt->execute([
            $username,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            json_encode($roles)
        ]);
    }
}
