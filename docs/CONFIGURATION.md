# ⚙️ Configuration Guide

Kompletný návod na konfiguráciu Mezzio Minimal aplikácie.

## 📋 Prehľad Konfigurácie

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

## 🎨 Theme Configuration

### AssetHelper Configuration

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

### Theme Manifest Structure

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

### Theme Usage v Templates

```php
// V handleroch
$assetHelper = $container->get(AssetHelper::class);
$templateData = [
    'cssUrl' => $assetHelper->css('bootstrap'),
    'jsUrl' => $assetHelper->js('bootstrap'),
    'themeInfo' => $assetHelper->getThemeInfo('bootstrap'),
];
```

## 🔐 Session Configuration

### Session Settings

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

### Production Session Settings

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

## 🗄️ Database Configuration

### SQLite Configuration (Development)

```php
// config/autoload/dependencies.global.php
'pdo.user' => function () {
    $dsn = 'sqlite:' . getcwd() . '/data/user.db';
    return new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
},

'pdo.mark' => function () {
    $dsn = 'sqlite:' . getcwd() . '/data/mark.db';
    return new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
},
```

### PostgreSQL Configuration (Production)

```php
// config/autoload/database.local.php (production)
return [
    'dependencies' => [
        'factories' => [
            'pdo.user' => function () {
                $dsn = 'pgsql:host=localhost;dbname=mezzio_users';
                return new PDO($dsn, 'username', 'password', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            },
            'pdo.mark' => function () {
                $dsn = 'pgsql:host=localhost;dbname=mezzio_marks';
                return new PDO($dsn, 'username', 'password', [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            },
        ],
    ],
];
```

## 🛡️ Security Configuration

### CSRF Configuration

```php
// config/autoload/dependencies.global.php
'csrf' => function () {
    return [
        'token_name' => 'csrf_token',
        'token_length' => 32,
        'expire_time' => 3600,  // 1 hour
    ];
},
```

### Path Security Configuration

```php
// PathService configuration
'path_service' => [
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'css', 'js'],
    'max_path_length' => 255,
    'blocked_patterns' => ['..', '\\', '<', '>', ':', '"', '|', '?', '*'],
],
```

## 🌐 Environment Configuration

### Development Environment

```php
// config/autoload/development.local.php
return [
    'debug' => true,
    'config_cache_enabled' => false,
    'zend-expressive' => [
        'error_handler' => [
            'template_404' => 'error::404',
            'template_error' => 'error::error',
        ],
    ],
];
```

### Production Environment

```php
// config/autoload/production.local.php
return [
    'debug' => false,
    'config_cache_enabled' => true,
    'zend-expressive' => [
        'error_handler' => [
            'template_404' => 'error::404',
            'template_error' => 'error::500',
        ],
    ],
];
```

## 📧 Email Configuration (Voliteľné)

### SMTP Configuration

```php
// config/autoload/mail.local.php
return [
    'mail' => [
        'transport' => [
            'type' => 'smtp',
            'options' => [
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'connection_class' => 'login',
                'connection_config' => [
                    'username' => 'your-email@gmail.com',
                    'password' => 'your-app-password',
                    'ssl' => 'tls',
                ],
            ],
        ],
        'message' => [
            'from' => 'your-email@gmail.com',
            'from_name' => 'Mezzio App',
        ],
    ],
];
```

## 🔧 Middleware Configuration

### Middleware Pipeline

```php
// config/autoload/middleware-pipeline.global.php
return [
    'middleware_pipeline' => [
        'always' => [
            'middleware' => [
                \Mezzio\Helper\ServerUrlMiddleware::class,
                \Mezzio\Helper\UrlHelperMiddleware::class,
            ],
            'priority' => 10000,
        ],
        'routing' => [
            'middleware' => [
                \Mezzio\Router\Middleware\RouteMiddleware::class,
            ],
            'priority' => 1,
        ],
        'dispatch' => [
            'middleware' => [
                \Mezzio\Router\Middleware\DispatchMiddleware::class,
            ],
            'priority' => 1,
        ],
    ],
];
```

### Custom Middleware

```php
// Pridanie custom middleware
'middleware_pipeline' => [
    'security' => [
        'middleware' => [
            \User\Middleware\CsrfMiddleware::class,
            \User\Middleware\AuthenticationMiddleware::class,
        ],
        'priority' => 100,
    ],
],
```

## 📝 Template Configuration

### Template Renderer

```php
// config/autoload/templates.global.php
return [
    'templates' => [
        'extension' => 'phtml',
        'paths' => [
            'app' => ['templates/app'],
            'error' => ['templates/error'],
            'layout' => ['templates/layout'],
            'user' => ['modules/User/templates'],
        ],
    ],
];
```

### Template Helpers

```php
// Registrácia template helpers
'template_helpers' => [
    'factories' => [
        'escapeHtml' => function () {
            return new \Laminas\Escaper\Escaper('utf-8');
        },
        'assetHelper' => function ($container) {
            return $container->get(AssetHelper::class);
        },
    ],
],
```

## 🚀 Build Configuration

### Composer Scripts

```json
// composer.json
{
    "scripts": {
        "serve": "php -S localhost:8080 -t public/",
        "build:production:package": "./build-production.sh",
        "build:staging": "./build-staging.sh",
        "clean:build": "rm -rf build/",
        "clean:themes": "find public/themes -name 'assets' -type d -exec rm -rf {} +",
        "build:themes:prod": [
            "cd themes/bootstrap && pnpm run build:prod",
            "cd themes/main && pnpm run build:prod"
        ]
    }
}
```

### Environment Variables

```bash
# .env (development)
APP_ENV=development
DEBUG=true
DATABASE_URL=sqlite:data/user.db
SESSION_SECURE=false

# .env.production
APP_ENV=production
DEBUG=false
DATABASE_URL=pgsql://user:pass@localhost/db
SESSION_SECURE=true
```

## 🔍 Debugging Configuration

### Error Handling

```php
// config/autoload/error-handler.global.php
return [
    'error_handler' => [
        'template_404' => 'error::404',
        'template_error' => 'error::error',
        'layout' => 'layout::default',
    ],
    'whoops' => [
        'json_exceptions' => [
            'display' => true,
            'show_trace' => true,
            'ajax_only' => true,
        ],
    ],
];
```

### Logging Configuration

```php
// config/autoload/logger.global.php
return [
    'logger' => [
        'handlers' => [
            'default' => [
                'type' => 'stream',
                'options' => [
                    'stream' => 'data/logs/app.log',
                    'level' => \Monolog\Logger::INFO,
                ],
            ],
        ],
    ],
];
```

## 📋 Configuration Checklist

### Development Setup
- [ ] Session configuration nastavená
- [ ] Database connections funkčné
- [ ] Theme assets building
- [ ] Error handling zapnuté
- [ ] Debug mode aktívny

### Production Setup
- [ ] Debug mode vypnutý
- [ ] Config cache zapnutý
- [ ] Session security zapnuté
- [ ] Database credentials bezpečné
- [ ] Error templates production-ready
- [ ] Logging nakonfigurované

---

## 📚 Súvisiace Dokumenty

### 🏗️ Architektúra a Development
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Architektúra systému a theme konfigurácia
- **[USER_MODULE.md](USER_MODULE.md)** - User modul konfigurácia
- **[API_REFERENCE.md](API_REFERENCE.md)** - API konfigurácia a usage

### 🚀 Production a Security
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment konfigurácie
- **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** - Bezpečnostné nastavenia
- **[APACHE_GUIDE.md](APACHE_GUIDE.md)** - Apache konfigurácia

### 🔧 Support a Údržba
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Riešenie konfiguračných problémov
- **[MAINTENANCE.md](MAINTENANCE.md)** - Monitoring konfigurácie
- **[QUICK_START.md](QUICK_START.md)** - Základné nastavenie

**Späť na hlavnú:** [README.md](README.md)
