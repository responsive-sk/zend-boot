# Mezzio Minimal Application

Minimálna Mezzio aplikácia vytvorená od základov s moderným PHP 8.4 a najlepšími praktikami.

## 🚀 Funkcie

- **Mezzio 3.21** - Moderný PHP middleware framework
- **FastRoute** - Rýchly routing
- **PSR-7/PSR-15** - HTTP message a middleware štandardy
- **Dependency Injection** - Laminas ServiceManager
- **Dev Tools** - PHPUnit, PHPStan, CodeSniffer, Rector

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

# Vytvor cache adresár
mkdir -p data/cache
```

## 🏃‍♂️ Spustenie

```bash
# Spusti development server
composer serve

# Alebo manuálne
php -S localhost:8080 -t public/ public/index.php
```

Aplikácia bude dostupná na `http://localhost:8080`

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
├── config/              # Konfigurácia
│   ├── config.php      # Hlavná konfigurácia
│   └── container.php   # DI container
├── public/             # Web root
│   └── index.php      # Entry point
├── src/               # Aplikačný kód
│   └── Handler/       # Request handlers
├── tests/             # PHPUnit testy
└── data/              # Cache a dáta
```

## 📝 Pridanie novej route

1. Vytvor handler v `src/Handler/`
2. Vytvor factory pre handler
3. Registruj factory v `config/config.php`
4. Pridaj route v `public/index.php`

## 🔧 Konfigurácia

- **PHPUnit**: `phpunit.xml`
- **PHPStan**: `phpstan.neon`
- **CodeSniffer**: `phpcs.xml`
- **Rector**: `rector.php`

## 📄 Licencia

MIT License
