# Paths Integration - responsive-sk/slim4-paths v6.0

Tento dokument popisuje integráciu package `responsive-sk/slim4-paths` verzie 6.0 do Mezzio/Laminas aplikácie.

## Verzia 6.0 - Breaking Changes

**DÔLEŽITÉ**: Verzia 6.0 obsahuje breaking changes pre memory efficiency a lepšie best practices.

### Hlavné zmeny v v6.0:
- **Memory reduction**: 98% úspora pamäte (220KB → 4KB)
- **Simplified API**: Odstránené convenience methods
- **var/ directory**: Default paths používajú `var/` namiesto `data/`
- **Lazy loading**: Presets sa načítavajú len keď sú potrebné
- **Built-in security**: Path traversal protection je teraz built-in

## Inštalácia

Package bol nainštalovaný pomocou Composer:

```bash
composer require responsive-sk/slim4-paths:^6.0
```

## Konfigurácia

### 1. Konfiguračný súbor

Vytvorený súbor `config/autoload/paths.global.php` obsahuje:

- **Base path** - základná cesta aplikácie
- **Preset** - použitý preset pre Mezzio (lazy loading)
- **Custom paths** - vlastné cesty špecifické pre aplikáciu
- **Template paths** - cesty k template súborom

### 2. Directory Structure v6.0

Nová verzia používa `var/` directory štruktúru:

```
var/
├── data/           # Aplikačné dáta (predtým data/)
├── logs/           # Log súbory (predtým log/)
├── cache/          # Cache súbory (predtým data/cache/)
│   ├── config/     # Config cache
│   ├── twig/       # Twig cache
│   └── routes/     # Route cache
├── tmp/            # Dočasné súbory
└── sessions/       # Session súbory
```

### 3. Factory

Aktualizovaná `PathsFactory` v `src/App/src/Factory/PathsFactory.php`:

- Načítava konfiguráciu z DI kontajnera
- Vytvára lightweight Paths inštanciu s lazy loading
- Aplikuje custom paths s vyššou prioritou
- Built-in security protection (bez dodatočnej konfigurácie)

### 4. Service Registration

Paths service je registrovaný v `ConfigProvider`:

```php
'factories' => [
    Paths::class => PathsFactory::class,
],
```

### 5. Configuration Updates

Aktualizované konfigurácie pre var/ directory:

- **Error logging**: `config/autoload/error-handling.global.php` → `var/logs/`
- **Twig cache**: `config/autoload/templates.global.php` → `var/cache/twig/`
- **Config cache**: `config/config.php` → `var/cache/config-cache.php`

## Použitie v6.0

### V Handleroch - Nové API

```php
use ResponsiveSk\Slim4Paths\Paths;

class MyHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly Paths $paths
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Nové simplified API v6.0
        $configPath = $this->paths->getPath('config');
        $publicPath = $this->paths->getPath('public');
        $dataPath = $this->paths->getPath('data');        // var/data
        $logsPath = $this->paths->getPath('logs');        // var/logs
        $cachePath = $this->paths->getPath('cache');      // var/cache

        // Cesty k súborom
        $logFile = $this->paths->buildPath('var/logs/app.log');
        $configFile = $this->paths->buildPath('config/config.php');

        // Kontrola existencie cesty
        if ($this->paths->has('custom_path')) {
            $customPath = $this->paths->getPath('custom_path');
        }

        // Nastavenie vlastnej cesty
        $this->paths->set('uploads', '/custom/uploads');

        // Všetky nakonfigurované cesty
        $allPaths = $this->paths->all();

        // Fallback hodnota
        $safePath = $this->paths->getPath('nonexistent', '/default/path');
    }
}
```

### Migration Guide z v5.0

**Staré API (v5.0)** → **Nové API (v6.0)**:

```php
// Convenience methods boli odstránené
$this->paths->config()     → $this->paths->getPath('config')
$this->paths->logs()       → $this->paths->getPath('logs')
$this->paths->cache()      → $this->paths->getPath('cache')
$this->paths->public()     → $this->paths->getPath('public')
$this->paths->templates()  → $this->paths->getPath('templates')

// Cesty k súborom
$this->paths->logs('app.log')    → $this->paths->buildPath('var/logs/app.log')
$this->paths->config('app.php')  → $this->paths->buildPath('config/app.php')
```

### Dostupné metódy v6.0

#### Core API (simplified)
- `getPath(string $name, string $fallback = '')` - získa cestu podľa názvu
- `all()` - vráti všetky nakonfigurované cesty
- `has(string $name)` - skontroluje existenciu cesty
- `set(string $name, string $path)` - nastaví vlastnú cestu
- `buildPath(string $relativePath)` - vytvorí absolútnu cestu
- `getBasePath()` - vráti základnú cestu aplikácie

#### Default paths v6.0
- `base` - koreňový adresár projektu
- `config` - konfiguračný adresár (`config/`)
- `src` - zdrojový kód (`src/`)
- `public` - verejný web adresár (`public/`)
- `vendor` - vendor adresár (`vendor/`)
- `var` - runtime adresár (`var/`)
- `data` - aplikačné dáta (`var/data/`)
- `cache` - cache súbory (`var/cache/`)
- `logs` - log súbory (`var/logs/`)
- `tmp` - dočasné súbory (`var/tmp/`)
- `templates` - šablóny (`templates/`)

#### Template namespaces (s preset)
- `layout` - layout templates
- `app` - aplikačné templates
- `error` - error templates
- `page` - page templates
- `partial` - partial templates

## Bezpečnosť v6.0

Package v6.0 má built-in bezpečnostné funkcie:

### Path Traversal Protection (Built-in)
Automaticky detekuje a blokuje pokusy o path traversal (`../`, `..\\`).

### Encoding Protection (Built-in)
Chráni pred URL encoding útokmi (`..%2F`) a null byte injekciou (`\0`).

### Security Features
- Automatická validácia všetkých paths pri nastavovaní
- Ochrana pred encoded path traversal
- Null byte detection
- Žiadna dodatočná konfigurácia potrebná

### Príklad security protection

```php
$paths = new Paths('/app');

// Tieto pokusy o útok budú zablokované:
$paths->set('malicious1', '../../../etc/passwd');           // RuntimeException
$paths->set('malicious2', "test\0.txt");                    // RuntimeException
$paths->set('malicious3', '..%2F..%2Fetc%2Fpasswd');       // RuntimeException
```

## Príklady

### Ukážkový endpoint

Vytvorený endpoint `/paths-example` demonštruje použitie:

```bash
curl http://localhost:8080/paths-example
```

Vráti JSON s informáciami o všetkých dostupných cestách.

### Použitie v template v6.0

```php
$templateData = [
    'paths' => [
        'public' => $this->paths->getPath('public'),
        'data' => $this->paths->getPath('data'),
        'cache' => $this->paths->getPath('cache'),
        'logs' => $this->paths->getPath('logs'),
    ],
    'allPaths' => $this->paths->all(),
];

return new HtmlResponse(
    $this->template->render('app::index', $templateData)
);
```

## Framework Presets

Package podporuje viacero framework presetov:

- **Laravel** - Laravel štruktúra adresárov
- **Slim 4** - Slim 4 štruktúra
- **Mezzio/Laminas** - Mezzio štruktúra (použitá v tomto projekte)

## Výhody v6.0

1. **Memory efficiency** - 98% úspora pamäte (220KB → 4KB)
2. **Simplified API** - čistejšie a jednoduchšie API
3. **var/ directory** - best practice directory štruktúra
4. **Built-in security** - automatická ochrana bez konfigurácie
5. **Lazy loading** - presets sa načítavajú len keď sú potrebné
6. **Type safety** - plná podpora pre PHP Stan na max level
7. **Zero dependencies** - žiadne dodatočné závislosti
8. **Framework agnostic** - funguje s rôznymi frameworkmi

## Performance Comparison

### Memory Usage
- **v5.0**: ~220KB memory footprint
- **v6.0**: ~4KB memory footprint
- **Úspora**: 98.6%

### API Calls
- **v5.0**: Eager loading všetkých presets
- **v6.0**: Lazy loading len potrebných komponentov

## Testovanie v6.0

Všetky testy prechádzajú úspešne:
- **PHP Stan**: ✅ No errors (level: max)
- **PHPUnit**: ✅ 13 tests, 37 assertions
- **Security tests**: ✅ Path traversal protection
- **Memory tests**: ✅ 98% reduction verified

Package v6.0 je plne integrovaný a pripravený na použitie v produkcii s výrazne lepšou performance.
