# Mezzio User Module Documentation

## Overview

Kompletný User modul pre Mezzio aplikácie s autentifikáciou, autorizáciou a session managementom. Implementovaný podľa oficiálnej Mezzio dokumentácie s modernou architektúrou a bezpečnostnými best practices.

## Features

- ✅ **Session-based Authentication** - Oficiálny Mezzio PhpSession adapter
- ✅ **Role-based Authorization** - RBAC systém s admin/user rolami
- ✅ **SQLite Database** - Oddelené databázy (user.db, mark.db)
- ✅ **CSRF Protection** - Bezpečnosť formulárov
- ✅ **Path Traversal Protection** - Centralizovaná validácia ciest
- ✅ **Template System** - Jednoduchý PHP template renderer
- ✅ **Migration System** - Automatická inicializácia databáz

## Architecture

```
modules/User/
├── src/
│   ├── Entity/User.php                    # User entity s rolami
│   ├── Service/
│   │   ├── UserRepository.php             # PDO-based repository
│   │   ├── AuthenticationService.php      # Core auth logic
│   │   └── MezzioUserRepository.php       # Mezzio adapter
│   ├── Handler/
│   │   ├── LoginHandler.php               # Login/logout handling
│   │   ├── DashboardHandler.php           # Protected dashboard
│   │   └── AdminHandler.php               # Admin panel
│   ├── Middleware/
│   │   ├── CsrfMiddleware.php              # CSRF protection
│   │   ├── RequireLoginMiddleware.php      # Auth guard
│   │   └── RequireRoleMiddleware.php       # Role guard
│   ├── Form/
│   │   ├── LoginForm.php                  # Login form validation
│   │   └── RegistrationForm.php           # Registration form
│   └── ConfigProvider.php                 # Module configuration
├── templates/user/                        # PHTML templates
└── test/                                  # Unit tests
```

## Installation & Setup

### 1. Dependencies

```bash
composer require \
    mezzio/mezzio-authentication \
    mezzio/mezzio-authentication-session \
    mezzio/mezzio-authorization \
    mezzio/mezzio-authorization-rbac \
    mezzio/mezzio-session \
    mezzio/mezzio-session-ext \
    mezzio/mezzio-csrf \
    laminas/laminas-form \
    laminas/laminas-validator \
    league/flysystem \
    league/flysystem-local
```

### 2. Database Migration

```bash
# Initialize databases and create default users
php bin/migrate.php
```

### 3. Configuration Files

Required configuration files:
- `config/autoload/authentication.global.php` - Authentication setup
- `config/autoload/session.global.php` - Session configuration
- `config/autoload/database.global.php` - Database connections
- `config/autoload/templates.global.php` - Template paths

## Configuration

### Authentication Configuration

```php
// config/autoload/authentication.global.php
return [
    'dependencies' => [
        'aliases' => [
            AuthenticationInterface::class => PhpSession::class,
            UserRepositoryInterface::class => \User\Service\MezzioUserRepository::class,
        ],
    ],
    'authentication' => [
        'redirect' => '/user/login',
        'username' => 'credential',
        'password' => 'password',
    ],
];
```

### Session Configuration

```php
// config/autoload/session.global.php
return [
    'session' => [
        'cookie_name' => 'PHPSESSID',
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'persistent' => true,
    ],
];
```

## Usage

### Default Users

Po migrácii sú vytvorení default používatelia:

| Username | Password | Roles | Description |
|----------|----------|-------|-------------|
| `admin` | `admin123` | admin, user | Administrátor s plnými právami |
| `user` | `user123` | user | Štandardný používateľ |
| `mark` | `mark123` | mark, user | Používateľ s mark právami |

### Routes

| Route | Methods | Middleware | Description |
|-------|---------|------------|-------------|
| `/user/login` | GET, POST | SessionMiddleware | Prihlásenie |
| `/user/register` | GET, POST | - | Registrácia |
| `/user/logout` | GET | - | Odhlásenie |
| `/user/dashboard` | GET | SessionMiddleware, AuthenticationMiddleware | Dashboard |
| `/user/admin` | GET | SessionMiddleware, AuthenticationMiddleware, RequireRoleMiddleware | Admin panel |

### Code Examples

#### Basic Authentication Check

```php
// In any handler
public function handle(ServerRequestInterface $request): ResponseInterface
{
    $user = $request->getAttribute(UserInterface::class);
    
    if (!$user) {
        return new RedirectResponse('/user/login');
    }
    
    // User is authenticated
    $username = $user->getIdentity();
    $roles = iterator_to_array($user->getRoles());
}
```

#### Role-based Access Control

```php
// Check if user has specific role
if (in_array('admin', iterator_to_array($user->getRoles()))) {
    // Admin-only functionality
}

// Using middleware in routes
$app->get('/admin-only', [
    \Mezzio\Session\SessionMiddleware::class,
    \Mezzio\Authentication\AuthenticationMiddleware::class,
    \User\Middleware\RequireRoleMiddleware::forRole($authorization, 'admin'),
    'App\Handler\AdminHandler'
]);
```

#### Working with User Repository

```php
// Get user repository
$userRepo = $container->get(\User\Service\UserRepository::class);

// Find users
$user = $userRepo->findByUsername('admin');
$user = $userRepo->findByEmail('user@example.com');
$allUsers = $userRepo->findAll();
$adminUsers = $userRepo->findByRole('admin');

// Create new user
$authService = $container->get(\User\Service\AuthenticationService::class);
$newUser = $authService->registerUser('newuser', 'email@example.com', 'password123');
```

## Security Features

### CSRF Protection

```php
// Automatic CSRF token generation in templates
<input type="hidden" name="csrf_token" value="<?= $escapeHtml($csrf_token ?? '') ?>">

// Validation in middleware
$app->route('/protected-form', [
    \User\Middleware\CsrfMiddleware::class,
    'App\Handler\FormHandler'
], ['GET', 'POST']);
```

### Path Traversal Protection

```php
// Safe file operations
$pathService = $container->get(\App\Service\PathService::class);

try {
    $safePath = $pathService->getPublicFilePath('uploads/file.txt');
    $content = file_get_contents($safePath);
} catch (\RuntimeException $e) {
    // Path traversal attempt blocked
}
```

### Password Security

```php
// Passwords are automatically hashed with PHP password_hash()
$user = new User('username', 'email@example.com', password_hash('password', PASSWORD_DEFAULT));

// Verification
if ($user->verifyPassword('submitted_password')) {
    // Password is correct
}
```

## Database Schema

### Users Table (user.db)

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    roles TEXT NOT NULL DEFAULT '[]',
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Marks Table (mark.db)

```sql
CREATE TABLE marks (
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
);
```

## Testing

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit modules/User/test/

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

### Test Authentication

```bash
# Test authentication directly
php debug_auth.php

# Test session functionality
php test_session.php

# Test login flow
php test_login.php
```

## Troubleshooting

### Common Issues

1. **Session not working**
   - Check if SessionMiddleware is in route pipeline
   - Verify session configuration in `session.global.php`

2. **Authentication fails**
   - Verify UserRepositoryInterface implementation
   - Check authentication configuration
   - Ensure database migration was run

3. **CSRF token errors**
   - Ensure CsrfMiddleware is properly configured
   - Check if session is available before CSRF middleware

4. **Path traversal errors**
   - Verify PathService configuration
   - Check file permissions in configured directories

### Debug Tools

```bash
# Check configuration
php -r "print_r(require 'config/config.php');"

# Test database connection
sqlite3 data/user.db "SELECT * FROM users;"

# Check session files
ls -la /tmp/sess_*
```

## Performance Considerations

- **SQLite** je vhodné pre development a malé aplikácie
- Pre production odporúčame **PostgreSQL** alebo **MySQL**
- Session súbory sa ukladajú v `/tmp/` - konfigurovať pre production
- Implementovať **Redis** pre session storage vo vysokej záťaži

## Security Best Practices

1. **Passwords** - Vždy hashované s `password_hash()`
2. **Sessions** - HTTPOnly cookies, secure v production
3. **CSRF** - Tokeny pre všetky formuláre
4. **Path Traversal** - Centralizovaná validácia ciest
5. **SQL Injection** - Prepared statements v PDO
6. **XSS** - Template escaping s `$escapeHtml()`

## Migration to Production

1. **Database** - Migrácia z SQLite na PostgreSQL/MySQL
2. **Session Storage** - Redis alebo database-based
3. **HTTPS** - Secure cookies, HSTS headers
4. **Environment** - Separate config pre production
5. **Monitoring** - Logging, error tracking

## Contributing

1. Fork repository
2. Create feature branch
3. Write tests
4. Follow PSR-12 coding standards
5. Submit pull request

## License

MIT License - see LICENSE file for details.
