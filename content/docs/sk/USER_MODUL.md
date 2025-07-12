# * User Modul

Kompletný User modul s autentifikáciou, autorizáciou a session managementom.

##  Prehľad

User modul implementuje moderný authentication systém s bezpečnostnými best practices.

**Status:** - Production Ready v2.0.1

##  Funkcie

-  **Session-based Authentication** - Mezzio PhpSession adapter
-  **Role-based Authorization** - RBAC systém (admin/user/mark)
-  **SQLite Database** - Oddelené databázy (user.db, mark.db)
-  **CSRF Protection** - Bezpečnosť formulárov
-  **Path Traversal Protection** - Validácia ciest
-  **Template System** - PHP template renderer s escaping
-  **Migration System** - Automatická inicializácia databáz

##  Architektúra

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

##  Inštalácia & Setup

### 1. Dependencies

```bash
composer require \
    mezzio/mezzio-authentication \
    mezzio/mezzio-authentication-session \
    mezzio/mezzio-authorization \
    mezzio/mezzio-authorization-rbac \
    mezzio/mezzio-session \
    mezzio/mezzio-session-ext \
    mezzio/mezzio-csrf
```

### 2. Database Migration

```bash
# Initialize databases and create default users
php bin/migrate.php
```

### 3. Konfiguračné súbory

- `config/autoload/authentication.global.php` - Authentication setup
- `config/autoload/session.global.php` - Session configuration
- `config/autoload/database.global.php` - Database connections
- `config/autoload/templates.global.php` - Template paths

##  Default Users

Po migrácii sú vytvorení títo používatelia:

| Username | Password | Roles | Popis |
|----------|----------|-------|-------|
| `admin` | `admin123` | admin, user | Plný administrátorský prístup |
| `user` | `user123` | user | Štandardný používateľ |
| `mark` | `mark123` | mark, user | Mark management prístup |

##  Authentication Flow

### 1. Login Process

```php
// LoginHandler.php
public function handle(ServerRequestInterface $request): ResponseInterface
{
    if ($request->getMethod() === 'POST') {
        $data = $request->getParsedBody();
        
        // CSRF validation
        if (!$this->csrfGuard->validateToken($data['_token'] ?? '')) {
            return $this->loginForm(['error' => 'Invalid CSRF token']);
        }
        
        // Authentication
        $result = $this->authService->authenticate($data['username'], $data['password']);
        
        if ($result->isValid()) {
            return new RedirectResponse('/user/dashboard');
        }
    }
    
    return $this->loginForm();
}
```

### 2. Session Management

```php
// Session configuration
'session' => [
    'cookie_name' => 'PHPSESSID',
    'cookie_httponly' => true,        // XSS protection
    'cookie_samesite' => 'Lax',       // CSRF protection
    'cookie_secure' => false,         // Set true for HTTPS
    'cache_expire' => 180,            // 3 hours
],
```

### 3. Authorization Guards

```php
// RequireLoginMiddleware.php
public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
{
    $user = $request->getAttribute(UserInterface::class);
    
    if (!$user) {
        return new RedirectResponse('/user/login');
    }
    
    return $handler->handle($request);
}
```

##  Bezpečnosť

### CSRF Protection

```php
// V templates
<input type="hidden" name="_token" value="<?= $csrfToken ?>" />

// V handleroch
if (!$this->csrfGuard->validateToken($data['_token'] ?? '')) {
    throw new InvalidArgumentException('Invalid CSRF token');
}
```

### Password Hashing

```php
// User entity
public function setPassword(string $password): void
{
    $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
}

public function verifyPassword(string $password): bool
{
    return password_verify($password, $this->passwordHash);
}
```

### Role-based Access

```php
// Route configuration
[
    'path' => '/user/admin',
    'middleware' => [
        RequireLoginMiddleware::class,
        RequireRoleMiddleware::class,  // Requires 'admin' role
        AdminHandler::class,
    ],
    'allowed_methods' => ['GET', 'POST'],
],
```

##  Routes & Endpoints

### Verejné Routes

- **`GET /user/login`** - Login formulár
- **`POST /user/login`** - Login spracovanie
- **`GET /user/register`** - Registračný formulár
- **`POST /user/register`** - Registrácia spracovanie

### Chránené Routes

- **`GET /user/dashboard`** - User dashboard (vyžaduje login)
- **`POST /user/logout`** - Logout (vyžaduje login)
- **`GET /user/admin`** - Admin panel (vyžaduje admin role)

##  Database Schema

### User Table

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    roles TEXT NOT NULL,  -- JSON array
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Mark Table

```sql
CREATE TABLE marks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

##  Testing

### Unit Tests

```bash
# Spusti user module testy
./vendor/bin/phpunit modules/User/test/

# Konkrétny test
./vendor/bin/phpunit modules/User/test/Service/AuthenticationServiceTest.php
```

### Integration Tests

```bash
# Test authentication flow
./vendor/bin/phpunit tests/Integration/UserAuthenticationTest.php
```

##  Customizácia

### Pridanie novej role

```php
// V User entity
public function addRole(string $role): void
{
    $roles = $this->getRoles();
    if (!in_array($role, $roles)) {
        $roles[] = $role;
        $this->roles = json_encode($roles);
    }
}
```

### Custom Authentication Adapter

```php
// Implementuj UserRepositoryInterface
class CustomUserRepository implements UserRepositoryInterface
{
    public function findByCredentials(string $credential, string $password = null): ?UserInterface
    {
        // Custom authentication logic
    }
}
```

---

**Ďalšie informácie:**
- [RYCHLY_START.md](RYCHLY_START.md) - Návod na spustenie
- [BEZPECNOST.md](BEZPECNOST.md) - Bezpečnostné nastavenia
- [KONFIGURACIA.md](KONFIGURACIA.md) - Konfiguračné možnosti
- [/docs/en/API_REFERENCE.md](../API_REFERENCE.md) - API dokumentácia
