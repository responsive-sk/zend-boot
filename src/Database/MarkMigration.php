<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

/**
 * Mark Database Migration
 * 
 * HDM Boot Protocol Compliant - Mark System Database
 * Creates mark.db with mark management tables
 */
class MarkMigration
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function migrate(): void
    {
        $this->createMarksTable();
        $this->createMarkCategoriesTable();
        $this->createMarkPermissionsTable();
        $this->seedDefaultCategories();
        
        echo "✅ Mark database migration completed\n";
    }

    private function createMarksTable(): void
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
                metadata TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                
                -- INDEX idx_user_id (user_id),
                -- INDEX idx_category (category),
                -- INDEX idx_status (status),
                -- INDEX idx_priority (priority),
                -- INDEX idx_created (created_at)
            )
        ";
        
        $this->pdo->exec($sql);

        // Create indexes separately
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_user_id ON marks(user_id)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_category ON marks(category)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_status ON marks(status)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_priority ON marks(priority)');
        $this->pdo->exec('CREATE INDEX IF NOT EXISTS idx_marks_created ON marks(created_at)');

        echo "  📝 Marks table created\n";
    }

    private function createMarkCategoriesTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS mark_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(100) UNIQUE NOT NULL,
                description TEXT,
                color VARCHAR(7) DEFAULT '#007bff',
                icon VARCHAR(50),
                is_active BOOLEAN DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                
                -- INDEX idx_name (name),
                -- INDEX idx_active (is_active)
            )
        ";
        
        $this->pdo->exec($sql);
        echo "  🏷️ Mark categories table created\n";
    }

    private function createMarkPermissionsTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS mark_permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                mark_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                permission_type VARCHAR(50) NOT NULL,
                granted_by INTEGER,
                granted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                expires_at DATETIME NULL,
                
                FOREIGN KEY (mark_id) REFERENCES marks(id) ON DELETE CASCADE,
                FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
                -- INDEX idx_mark_id (mark_id),
                -- INDEX idx_user_id (user_id),
                -- INDEX idx_permission (permission_type),
                -- INDEX idx_expires (expires_at)
            )
        ";
        
        $this->pdo->exec($sql);
        echo "  🔐 Mark permissions table created\n";
    }

    private function seedDefaultCategories(): void
    {
        // Check if categories already exist
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM mark_categories');
        $stmt->execute();
        $categoryCount = $stmt->fetchColumn();

        if ($categoryCount == 0) {
            echo "  🌱 Seeding default mark categories...\n";
            
            $defaultCategories = [
                ['general', 'General marks', '#007bff', 'fas fa-bookmark'],
                ['important', 'Important marks', '#dc3545', 'fas fa-exclamation'],
                ['work', 'Work related marks', '#28a745', 'fas fa-briefcase'],
                ['personal', 'Personal marks', '#6f42c1', 'fas fa-user'],
                ['project', 'Project marks', '#fd7e14', 'fas fa-project-diagram'],
                ['archive', 'Archived marks', '#6c757d', 'fas fa-archive'],
            ];

            $stmt = $this->pdo->prepare('
                INSERT INTO mark_categories (name, description, color, icon)
                VALUES (?, ?, ?, ?)
            ');

            foreach ($defaultCategories as $category) {
                $stmt->execute($category);
                echo "    🏷️ Created category: {$category[0]}\n";
            }

            echo "  ✅ Default categories created\n";
        } else {
            echo "  ℹ️ Categories already exist, skipping seeding\n";
        }
    }
}
