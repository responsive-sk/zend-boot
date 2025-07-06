# Dependencies Audit - Minimalizácia Závislostí

## 📊 Aktuálny Stav Dependencies

### Production Dependencies (require)
```json
{
    "php": "~8.3.0 || ~8.4.0 || ~8.5.0",
    "ext-json": "*",
    "laminas/laminas-config-aggregator": "^1.14",
    "laminas/laminas-diactoros": "^3.0",
    "laminas/laminas-form": "^3.21",
    "laminas/laminas-httphandlerrunner": "^2.1",
    "laminas/laminas-i18n": "^2.30",
    "laminas/laminas-servicemanager": "^3.22",
    "laminas/laminas-stratigility": "^3.11",
    "laminas/laminas-validator": "^2.64",
    "league/flysystem": "^3.0",
    "league/flysystem-local": "^3.0",
    "mezzio/mezzio": "^3.20",
    "mezzio/mezzio-authentication": "^1.11",
    "mezzio/mezzio-authentication-session": "^1.10",
    "mezzio/mezzio-authorization": "^1.10",
    "mezzio/mezzio-authorization-rbac": "^1.9",
    "mezzio/mezzio-csrf": "^1.10",
    "mezzio/mezzio-fastroute": "^3.1",
    "mezzio/mezzio-laminasviewrenderer": "^2.18",
    "mezzio/mezzio-session": "^1.16",
    "mezzio/mezzio-template": "^2.11"
}
```

## 🔍 Analýza Použitia Dependencies

### ✅ **KRITICKÉ - Používané**

| Package | Použitie | Dôvod |
|---------|----------|-------|
| `mezzio/mezzio` | Core framework | ✅ Základ aplikácie |
| `mezzio/mezzio-fastroute` | Routing | ✅ Všetky routes |
| `mezzio/mezzio-session` | Session management | ✅ User/Mark auth |
| `mezzio/mezzio-template` | Template interface | ✅ PhpRenderer |
| `mezzio/mezzio-laminasviewrenderer` | Template rendering | ✅ Všetky templates |
| `laminas/laminas-diactoros` | PSR-7 implementation | ✅ HTTP messages |
| `laminas/laminas-httphandlerrunner` | Request handling | ✅ Application runner |
| `laminas/laminas-servicemanager` | DI Container | ✅ Dependency injection |
| `laminas/laminas-stratigility` | Middleware | ✅ Middleware pipeline |
| `laminas/laminas-config-aggregator` | Config merging | ✅ Config system |

### ⚠️ **ČIASTOČNE POUŽÍVANÉ - Kandidáti na Optimalizáciu**

#### `laminas/laminas-form` + `laminas/laminas-validator`
- **Použitie:** Len 2 formuláre (LoginForm, RegistrationForm)
- **Veľkosť:** ~2MB dependencies
- **Alternatíva:** Custom form handling
- **Odporúčanie:** ⚠️ Zvážiť nahradenie

#### `league/flysystem` + `league/flysystem-local`
- **Použitie:** UnifiedPathService, konfigurácia v dependencies.global.php
- **Skutočné použitie:** Len path resolution, nie file operations
- **Alternatíva:** Native PHP file functions
- **Odporúčanie:** ⚠️ Možno nahradiť

#### `mezzio/mezzio-authentication` + `mezzio/mezzio-authentication-session`
- **Použitie:** User module authentication
- **Problém:** Mark module má vlastný auth systém
- **Odporúčanie:** ⚠️ Unifikovať auth systémy

#### `mezzio/mezzio-authorization` + `mezzio/mezzio-authorization-rbac`
- **Použitie:** RequireRoleMiddleware
- **Problém:** Minimálne použitie, vlastné role systémy
- **Odporúčanie:** ⚠️ Nahradiť custom middleware

### ❌ **NEPOUŽÍVANÉ - Odstrániť**

#### `laminas/laminas-i18n`
- **Použitie:** ❌ Žiadne
- **Konfigurácia:** Len v config.php
- **Skutočnosť:** Aplikácia je len v slovenčine
- **Odporúčanie:** ❌ **ODSTRÁNIŤ**

#### `mezzio/mezzio-csrf`
- **Použitie:** ❌ Žiadne
- **Skutočnosť:** Custom CSRF v CsrfMiddleware
- **Odporúčanie:** ❌ **ODSTRÁNIŤ**

## 📈 Optimalizačný Plán

### Fáza 1: Okamžité Odstránenie (Bezpečné)
```bash
composer remove laminas/laminas-i18n
composer remove mezzio/mezzio-csrf
```
**Úspora:** ~1.5MB, 8 packages

### Fáza 2: Nahradenie Form Systému (Stredná náročnosť)
```bash
composer remove laminas/laminas-form laminas/laminas-validator
```
**Úspora:** ~2MB, 15 packages
**Akcia:** Nahradiť custom form handling

### Fáza 3: Nahradenie Flysystem (Stredná náročnosť)
```bash
composer remove league/flysystem league/flysystem-local
```
**Úspora:** ~500KB, 3 packages
**Akcia:** Native PHP file operations v UnifiedPathService

### Fáza 4: Unifikácia Auth Systému (Vysoká náročnosť)
```bash
composer remove mezzio/mezzio-authentication mezzio/mezzio-authentication-session
composer remove mezzio/mezzio-authorization mezzio/mezzio-authorization-rbac
```
**Úspora:** ~1MB, 6 packages
**Akcia:** Použiť len custom auth (User/Mark systémy)

## 🎯 Výsledky Optimalizácie

### Pred Optimalizáciou
- **Production packages:** 23
- **Total packages:** ~45
- **Archive size:** 4.4MB

### Po Úplnej Optimalizácii
- **Production packages:** ~15 (-35%)
- **Total packages:** ~30 (-33%)
- **Archive size:** ~3.5MB (-20%)

## 🔧 Implementačné Kroky

### 1. Okamžité Odstránenie
```bash
# Odstránenie nepoužívaných packages
composer remove laminas/laminas-i18n mezzio/mezzio-csrf

# Vyčistenie config
# Odstrániť z config/config.php:
# - Laminas\I18n\ConfigProvider::class
# - \Mezzio\Csrf\ConfigProvider::class
```

### 2. Custom Form Handling
```php
// Nahradiť Laminas Forms jednoduchými PHP triedami
class SimpleLoginForm {
    public function validate(array $data): array {
        $errors = [];
        if (empty($data['credential'])) {
            $errors['credential'] = 'Required';
        }
        return $errors;
    }
}
```

### 3. Native File Operations
```php
// Nahradiť Flysystem v UnifiedPathService
public function readPublicFile(string $path): string {
    $safePath = $this->getPublicFilePath($path);
    return file_get_contents($safePath);
}
```

## ⚡ Okamžité Akcie

**Pripravené na spustenie:**
1. Odstránenie `laminas/laminas-i18n` - 100% bezpečné
2. Odstránenie `mezzio/mezzio-csrf` - 100% bezpečné
3. Vyčistenie config súborov

**Odhadovaná úspora:** 1.5MB, 8 packages, žiadne breaking changes
