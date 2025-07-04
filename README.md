# Mezzio User Management Application

Kompletná Mezzio aplikácia s user managementom, autentifikáciou a modernou bezpečnosťou.

## 🚀 Funkcie

- ✅ **User Authentication** - Session-based prihlásenie s Mezzio PhpSession
- ✅ **Role-based Authorization** - RBAC systém s admin/user rolami
- ✅ **SQLite Database** - Oddelené databázy pre users a application data
- ✅ **CSRF Protection** - Kompletná bezpečnosť formulárov
- ✅ **Path Traversal Protection** - Bezpečné file operácie s Flysystem
- ✅ **Template System** - Jednoduchý PHP template renderer bez cache
- ✅ **Migration System** - Automatická inicializácia databáz
- ✅ **Bootstrap 5** - Responzívny UI framework
- ✅ **Security Best Practices** - Password hashing, session security, XSS protection

## 📋 Požiadavky

- PHP 8.3+ (testované s PHP 8.4)
- Composer

## 🛠️ Inštalácia

```bash
# Klonuj repository
git clone <repository-url>
cd mezzio-minimal

# Nainštaluj dependencies
composer install

# Inicializuj databázy a vytvor default users
php bin/migrate.php

# Spusti development server
php -S localhost:8080 -t public/
```

Aplikácia bude dostupná na `http://localhost:8080`

## 👤 Default Users

| Username | Password | Roles | Popis |
|----------|----------|-------|-------|
| `admin` | `admin123` | admin, user | Plný administrátorský prístup |
| `user` | `user123` | user | Štandardný používateľ |
| `mark` | `mark123` | mark, user | Mark management prístup |

## 🗺️ Dostupné Routes

### Verejné Routes
- `/` - Domovská stránka s Bootstrap demo
- `/bootstrap-demo` - Bootstrap komponenty showcase
- `/main-demo` - Hlavná aplikácia demo
- `/user/login` - Prihlásenie používateľa
- `/user/register` - Registrácia používateľa
- `/debug` - Debug informácie (development)

### Chránené Routes
- `/user/dashboard` - User dashboard (vyžaduje prihlásenie)
- `/user/admin` - Admin panel (vyžaduje admin rolu)
- `/user/logout` - Odhlásenie

## 🧪 Development

### Dostupné Composer scripty

```bash
# Spusti všetky kontroly
composer check

# Testovanie
composer test
composer test-coverage

# Coding standards
composer cs-check
composer cs-fix

# Statická analýza
composer analyze

# Refaktorovanie
composer rector
composer rector-fix

# Development server
composer serve
```

### Štruktúra projektu

```
├── public/              # Web root
│   ├── index.php       # Application entry point
│   └── assets/         # CSS, JS, images
├── src/                # Core application
│   ├── Handler/        # Request handlers
│   ├── Helper/         # View helpers
│   ├── Service/        # Business logic
│   ├── Template/       # Template renderer
│   └── Database/       # Database services
├── modules/User/       # User management module
│   ├── src/
│   │   ├── Entity/     # User entity
│   │   ├── Service/    # Authentication services
│   │   ├── Handler/    # Login, dashboard handlers
│   │   ├── Middleware/ # Auth & security middleware
│   │   └── Form/       # Form validation
│   ├── templates/      # User module templates
│   └── test/           # Unit tests
├── config/             # Configuration
│   └── autoload/       # Auto-loaded configs
├── data/               # SQLite databases
├── docs/               # Documentation
└── bin/                # CLI scripts
```

## 📚 Dokumentácia

- **[User Module Guide](docs/USER_MODULE.md)** - Kompletný návod na User modul
- **[API Reference](docs/API_REFERENCE.md)** - Detailná API dokumentácia
- **[Deployment Guide](docs/DEPLOYMENT.md)** - Production deployment návod

## 🔒 Bezpečnosť

- **Password Hashing** - PHP password_hash() s bcrypt
- **Session Security** - HTTPOnly cookies, session regeneration
- **CSRF Protection** - Tokeny pre všetky formuláre
- **Path Traversal Protection** - Centralizovaná validácia ciest
- **SQL Injection Protection** - Prepared statements
- **XSS Protection** - Template escaping

## 🧪 Testovanie

```bash
# Spusti všetky testy
composer test

# Test s coverage
composer test-coverage

# Špecifické testy
./vendor/bin/phpunit modules/User/test/
```

## 📝 Poznámky

- Aplikácia používa **PSR-7** HTTP messages
- **PSR-15** middleware pattern
- **PSR-11** dependency injection
- **Mezzio authentication** podľa oficiálnej dokumentácie
- Kód dodržiava **PSR-12** coding štandardy
- **SQLite** pre development, **PostgreSQL/MySQL** pre production

## 📄 Licencia

MIT License
