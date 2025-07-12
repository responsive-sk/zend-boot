# Konfigurácia

Návod na konfiguráciu Mezzio Minimal aplikácie.

## Prehľad

Aplikácia používa štandardnú Mezzio konfiguráciu s rozšíreniami pre theme systém a bezpečnosť.

### Konfiguračné Súbory

```
config/
├── autoload/
│   ├── dependencies.global.php    # DI container
│   ├── middleware-pipeline.global.php  # Middleware stack
│   ├── routes.global.php          # Application routes
│   ├── session.global.php         # Session configuration
│   ├── templates.global.php       # Template configuration
│   └── zend-expressive.global.php # Core Mezzio config
├── config.php                     # Main config aggregator
└── container.php                  # DI container setup
```

## Theme Konfigurácia

### AssetHelper Setup

```php
// config/autoload/dependencies.global.php
'factories' => [
    AssetHelper::class => function ($container) {
        return new AssetHelper(
            publicPath: 'public/themes',
            manifestPath: 'public/themes/%s/assets/manifest.json'
        );
    },
],
```

### Theme Manifest

```json
// public/themes/bootstrap/assets/manifest.json
{
  "main.css": {
    "file": "main-D30XL3Ms.css",
    "src": "src/style.css"
  },
  "main.js": {
    "file": "main-Df2FmC7f.js",
    "src": "src/main.js"
  }
}
```

### Použitie v Templates

```php
// V handleroch
$assetHelper = $container->get(AssetHelper::class);
$templateData = [
    'cssUrl' => $assetHelper->css('bootstrap'),
    'jsUrl' => $assetHelper->js('bootstrap'),
    'themeInfo' => $assetHelper->getThemeInfo('bootstrap'),
];
```

##  Session Konfigurácia

### Development Settings

```php
// config/autoload/session.global.php
return [
    'session' => [
        'cookie_name' => 'PHPSESSID',
        'cookie_httponly' => true,        // XSS protection
        'cookie_samesite' => 'Lax',       // CSRF protection
        'cookie_secure' => false,         // Set true for HTTPS
        'cookie_lifetime' => 0,           // Session cookie
        'persistent' => true,
        'cache_expire' => 180,            // 3 hours
        'gc_maxlifetime' => 10800,        // 3 hours
    ],
];
```

### Production Settings

```php
// config/autoload/session.local.php (production)
return [
    'session' => [
        'cookie_secure' => true,          // HTTPS only
        'cookie_samesite' => 'Strict',    // Stricter CSRF protection
        'cache_expire' => 60,             // 1 hour
        'gc_maxlifetime' => 3600,         // 1 hour
    ],
];
```

##  Databáza Konfigurácia

### SQLite Development

```php
// config/autoload/database.global.php
return [
    'database' => [
        'user' => [
            'dsn' => 'sqlite:' . __DIR__ . '/../../data/user.db',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],
        'mark' => [
            'dsn' => 'sqlite:' . __DIR__ . '/../../data/mark.db',
        ],
    ],
];
```

### Production MySQL

```php
// config/autoload/database.local.php (production)
return [
    'database' => [
        'user' => [
            'dsn' => 'mysql:host=localhost;dbname=app_users;charset=utf8mb4',
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            ],
        ],
    ],
];
```

##  Bezpečnostná Konfigurácia

### Content Security Policy

```php
// config/autoload/security.global.php
return [
    'security' => [
        'csp' => [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-eval'",  // Alpine.js
            'style-src' => "'self' 'unsafe-inline'", // Bootstrap
            'img-src' => "'self' data:",
            'font-src' => "'self'",
        ],
    ],
];
```

### CSRF Protection

```php
// V middleware pipeline
'csrf' => [
    'token_name' => '_token',
    'header_name' => 'X-CSRF-Token',
    'expire_time' => 3600,
],
```

##  Environment Konfigurácia

### Development (.env.local)

```bash
APP_ENV=development
DEBUG=true
LOG_LEVEL=debug
SESSION_SECURE=false
```

### Production (.env.local)

```bash
APP_ENV=production
DEBUG=false
LOG_LEVEL=error
SESSION_SECURE=true
DB_USER=app_user
DB_PASS=secure_password
```

##  Email Konfigurácia

### SMTP Settings

```php
// config/autoload/mail.global.php
return [
    'mail' => [
        'transport' => [
            'type' => 'smtp',
            'options' => [
                'host' => getenv('SMTP_HOST'),
                'port' => getenv('SMTP_PORT'),
                'username' => getenv('SMTP_USER'),
                'password' => getenv('SMTP_PASS'),
                'ssl' => 'tls',
            ],
        ],
        'from' => [
            'email' => 'noreply@example.com',
            'name' => 'Mezzio App',
        ],
    ],
];
```

##  Cache Konfigurácia

### File Cache

```php
// config/autoload/cache.global.php
return [
    'cache' => [
        'adapter' => 'filesystem',
        'options' => [
            'cache_dir' => 'data/cache',
            'ttl' => 3600,
        ],
    ],
];
```

### Redis Cache (Production)

```php
// config/autoload/cache.local.php
return [
    'cache' => [
        'adapter' => 'redis',
        'options' => [
            'server' => [
                'host' => 'localhost',
                'port' => 6379,
            ],
            'ttl' => 3600,
        ],
    ],
];
```

##  Logging Konfigurácia

### Development Logging

```php
// config/autoload/logger.global.php
return [
    'logger' => [
        'handlers' => [
            'file' => [
                'type' => 'stream',
                'options' => [
                    'stream' => 'var/logs/app.log',
                    'level' => 'debug',
                ],
            ],
        ],
    ],
];
```

---

**Ďalšie informácie:**
- [RYCHLY_START.md](RYCHLY_START.md) - Návod na spustenie
- [ARCHITEKTURA.md](ARCHITEKTURA.md) - Architektúra systému
- [BEZPECNOST.md](BEZPECNOST.md) - Bezpečnostné nastavenia
- [../API_REFERENCE.md](../API_REFERENCE.md) - API dokumentácia
