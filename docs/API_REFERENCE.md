# User Module API Reference

## Classes

### User\Entity\User

Hlavná User entita s kompletnou funkcionalitou.

#### Constructor

```php
public function __construct(
    string $username,
    string $email,
    string $passwordHash,
    array $roles = ['user']
)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getId()` | `?int` | Vráti ID používateľa |
| `setId(int $id)` | `void` | Nastaví ID používateľa |
| `getUsername()` | `string` | Vráti username |
| `setUsername(string $username)` | `void` | Nastaví username |
| `getEmail()` | `string` | Vráti email |
| `setEmail(string $email)` | `void` | Nastaví email |
| `getPasswordHash()` | `string` | Vráti password hash |
| `setPasswordHash(string $hash)` | `void` | Nastaví password hash |
| `verifyPassword(string $password)` | `bool` | Overí heslo |
| `getRoles()` | `array` | Vráti pole rolí |
| `setRoles(array $roles)` | `void` | Nastaví role |
| `hasRole(string $role)` | `bool` | Skontroluje, či má rolu |
| `addRole(string $role)` | `void` | Pridá rolu |
| `removeRole(string $role)` | `void` | Odstráni rolu |
| `isActive()` | `bool` | Vráti stav aktívnosti |
| `setActive(bool $active)` | `void` | Nastaví stav aktívnosti |
| `getCreatedAt()` | `\DateTimeImmutable` | Vráti dátum vytvorenia |
| `getLastLoginAt()` | `?\DateTimeImmutable` | Vráti posledné prihlásenie |
| `setLastLoginAt(\DateTimeImmutable $date)` | `void` | Nastaví posledné prihlásenie |
| `toArray()` | `array` | Konvertuje na pole |

### User\Service\UserRepository

PDO-based repository pre prácu s používateľmi.

#### Constructor

```php
public function __construct(private PDO $pdo)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `findById(int $id)` | `?User` | Nájde používateľa podľa ID |
| `findByUsername(string $username)` | `?User` | Nájde používateľa podľa username |
| `findByEmail(string $email)` | `?User` | Nájde používateľa podľa email |
| `save(User $user)` | `User` | Uloží používateľa |
| `delete(int $id)` | `bool` | Zmaže používateľa |
| `findAll()` | `array` | Vráti všetkých používateľov |
| `findByRole(string $role)` | `array` | Nájde používateľov podľa role |
| `usernameExists(string $username)` | `bool` | Skontroluje existenciu username |
| `emailExists(string $email)` | `bool` | Skontroluje existenciu email |

### User\Service\AuthenticationService

Hlavná služba pre autentifikáciu.

#### Constructor

```php
public function __construct(private UserRepository $userRepository)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `authenticate(string $credential, ?string $password)` | `?UserInterface` | Autentifikuje používateľa |
| `findByCredential(string $credential)` | `?User` | Nájde používateľa podľa credential |
| `registerUser(string $username, string $email, string $password, array $roles)` | `User` | Registruje nového používateľa |
| `changePassword(User $user, string $newPassword)` | `void` | Zmení heslo |
| `deactivateUser(User $user)` | `void` | Deaktivuje používateľa |
| `activateUser(User $user)` | `void` | Aktivuje používateľa |

### User\Service\AuthenticatedUser

Wrapper pre Mezzio UserInterface.

#### Constructor

```php
public function __construct(private User $user)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getIdentity()` | `string` | Vráti identitu (username) |
| `getRoles()` | `iterable` | Vráti role |
| `getDetail(string $name, $default)` | `mixed` | Vráti detail |
| `getDetails()` | `array` | Vráti všetky detaily |
| `getUser()` | `User` | Vráti User entitu |

## Handlers

### User\Handler\LoginHandler

Spracováva prihlásenie používateľov.

#### Constructor

```php
public function __construct(
    private TemplateRendererInterface $template,
    private PhpSession $adapter
)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `handle(ServerRequestInterface $request)` | `ResponseInterface` | Spracuje login request |

#### Request Flow

1. **GET** - Zobrazí login formulár
2. **POST** - Spracuje credentials a presmeruje

### User\Handler\DashboardHandler

Zobrazuje dashboard pre prihlásených používateľov.

#### Constructor

```php
public function __construct(private TemplateRendererInterface $template)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `handle(ServerRequestInterface $request)` | `ResponseInterface` | Zobrazí dashboard |

### User\Handler\LogoutHandler

Spracováva odhlásenie používateľov.

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `handle(ServerRequestInterface $request)` | `ResponseInterface` | Odhlási používateľa |

## Middleware

### User\Middleware\CsrfMiddleware

CSRF ochrana pre formuláre.

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `process(ServerRequestInterface $request, RequestHandlerInterface $handler)` | `ResponseInterface` | Spracuje CSRF validáciu |

#### Features

- Generuje CSRF tokeny pre GET requesty
- Validuje tokeny pre POST/PUT/DELETE requesty
- Ukladá tokeny v session
- Podporuje header `X-CSRF-Token`

### User\Middleware\RequireLoginMiddleware

Vyžaduje prihlásenie pre prístup.

#### Constructor

```php
public function __construct(
    private AuthenticationInterface $authentication,
    private string $redirectPath = '/user/login'
)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `process(ServerRequestInterface $request, RequestHandlerInterface $handler)` | `ResponseInterface` | Kontroluje prihlásenie |

### User\Middleware\RequireRoleMiddleware

Vyžaduje špecifickú rolu pre prístup.

#### Constructor

```php
public function __construct(
    private AuthorizationInterface $authorization,
    private array $requiredRoles = []
)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `process(ServerRequestInterface $request, RequestHandlerInterface $handler)` | `ResponseInterface` | Kontroluje role |
| `forRole(AuthorizationInterface $authorization, string $role)` | `self` | Factory pre jednu rolu |
| `forRoles(AuthorizationInterface $authorization, array $roles)` | `self` | Factory pre viac rolí |

## Forms

### User\Form\LoginForm

Formulár pre prihlásenie.

#### Fields

| Field | Type | Validation |
|-------|------|------------|
| `credential` | `Text` | Required, 3-255 chars |
| `password` | `Password` | Required, min 6 chars |
| `remember_me` | `Checkbox` | Optional |
| `csrf_token` | `Hidden` | Required |

### User\Form\RegistrationForm

Formulár pre registráciu.

#### Fields

| Field | Type | Validation |
|-------|------|------------|
| `username` | `Text` | Required, 3-50 chars, alphanumeric |
| `email` | `Email` | Required, valid email |
| `password` | `Password` | Required, 8+ chars, complex |
| `password_confirm` | `Password` | Required, must match password |
| `accept_terms` | `Checkbox` | Required |
| `csrf_token` | `Hidden` | Required |

## Database Services

### App\Database\MigrationService

Spravuje databázové migrácie.

#### Constructor

```php
public function __construct(
    private PDO $userPdo,
    private PDO $markPdo
)
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `migrate()` | `void` | Spustí všetky migrácie |

### App\Database\PdoFactory

Factory pre PDO connections.

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `__invoke(ContainerInterface $container, string $requestedName)` | `PDO` | Vytvorí PDO connection |

#### Supported Services

- `pdo.user` - Connection k user.db
- `pdo.mark` - Connection k mark.db

## Template System

### App\Template\PhpRenderer

Jednoduchý PHP template renderer.

#### Constructor

```php
public function __construct(array $config = [])
```

#### Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `render(string $name, $params)` | `string` | Renderuje template |
| `addPath(string $path, ?string $namespace)` | `void` | Pridá template path |
| `getPaths()` | `array` | Vráti template paths |

#### Template Helpers

```php
// V template súboroch
$escapeHtml($value)  // HTML escaping
```

## Configuration

### Authentication Config

```php
'authentication' => [
    'redirect' => '/user/login',    // Redirect URL pre neautentifikovaných
    'username' => 'credential',     // Pole pre username
    'password' => 'password',       // Pole pre password
]
```

### Session Config

```php
'session' => [
    'cookie_name' => 'PHPSESSID',
    'cookie_domain' => '',
    'cookie_path' => '/',
    'cookie_secure' => false,       // true v production
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'persistent' => true,
]
```

### Database Config

```php
'database' => [
    'user' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../../data/user.db',
    ],
    'mark' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../../data/mark.db',
    ],
]
```

## Events & Hooks

### Authentication Events

```php
// Po úspešnom prihlásení
$user->setLastLoginAt(new \DateTimeImmutable());

// Po registrácii
$session->set('flash_success', 'Registration successful!');
```

### Session Events

```php
// Pri prihlásení
session_regenerate_id(true);

// Pri odhlásení
session_destroy();
```

## Error Handling

### Common Exceptions

| Exception | When Thrown |
|-----------|-------------|
| `\InvalidArgumentException` | Neplatné dáta (duplicitný username/email) |
| `\RuntimeException` | Chyba session, template not found |
| `\PDOException` | Databázové chyby |

### Error Responses

| Status | Description |
|--------|-------------|
| `401` | Unauthorized - chýba autentifikácia |
| `403` | Forbidden - nedostatočné oprávnenia |
| `404` | Not Found - template/resource not found |
| `500` | Internal Error - systémová chyba |
