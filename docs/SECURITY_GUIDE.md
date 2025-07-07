# 🔒 Security Guide - Mezzio Minimal

Kompletný bezpečnostný návod pre Mezzio Minimal aplikáciu.

## 📋 Prehľad Bezpečnostných Opatrení

Táto aplikácia implementuje viacvrstvovú bezpečnosť:

1. **Path Traversal Protection** - PathService a Flysystem
2. **Content Security Policy** - CSP headers pre XSS ochranu
3. **Apache Security** - .htaccess konfigurácia
4. **Session Security** - Secure session handling
5. **CSRF Protection** - Token-based form protection
6. **Database Security** - Prepared statements, isolation

## 🛡️ Path Traversal Protection

### PathService Implementation

Centralizovaná služba pre bezpečnú prácu s cestami:

```php
// ✅ BEZPEČNÉ - Použitie PathService
$pathService = $container->get(PathService::class);
$safePath = $pathService->getPublicFilePath('user/avatar.jpg');

// ❌ NEBEZPEČNÉ - Priama manipulácia ciest
$path = '../storage/database.db';
```

### Validácia Ciest

PathService automaticky:
- **Odstráni nebezpečné znaky**: `..`, `\`, `<>:"|?*`
- **Realpath validácia**: Kontrola, či finálna cesta je v rámci povoleného adresára
- **Normalizácia ciest**: Automatické čistenie a formátovanie

### Flysystem Integrácia

```php
// Bezpečné čítanie súboru
$content = $pathService->readPublicFile('safe/path.txt');

// Kontrola existencie súboru
if ($pathService->publicFileExists('image.jpg')) {
    // Súbor existuje a je bezpečný
}
```

### AssetHelper Security

```php
// Automaticky validované cesty v AssetHelper
$assetHelper = $container->get(AssetHelper::class);
$cssUrl = $assetHelper->css('bootstrap');  // ✅ Bezpečné
$jsUrl = $assetHelper->js('main');         // ✅ Bezpečné
$imageUrl = $assetHelper->image('main', 'logo'); // ✅ Bezpečné
```

### Zakázané Vzory

Tieto vzory sú automaticky blokované:
- `../../../etc/passwd`
- `folder/../../../sensitive.txt`
- `folder\\..\\..\sensitive.txt`
- `file<script>.txt`
- Akékoľvek cesty s `..` segmentmi

## 🔐 Content Security Policy (CSP)

### Súčasná CSP Konfigurácia

```apache
Content-Security-Policy: base-uri 'self';
default-src 'self';
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
img-src 'self' data: blob: https://picsum.photos;
font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com;
connect-src 'self';
frame-ancestors 'none';
upgrade-insecure-requests
```

### Framework Requirements

#### Alpine.js Requirements
Alpine.js potrebuje `'unsafe-eval'` pretože:
- Kompiluje reactive expressions ako `x-data="{ open: false }"`
- Používa `new Function()` pre expression evaluation
- Vytvára dynamické reactive bindings

#### Bootstrap Requirements
Bootstrap potrebuje `blob:` v `img-src` pretože:
- Môže generovať blob URLs pre dynamické obrázky
- Používa blob URLs pre určité komponenty
- Vytvára temporary object URLs pre image processing

#### Lorem Picsum Requirements
Lorem Picsum potrebuje `https://picsum.photos` pretože:
- Poskytuje placeholder obrázky z externej domény
- Používa sa pre portfolio showcase a demo content

### Alternatívne CSP Možnosti

#### 1. Strict CSP (Najbezpečnejšie - Alpine.js nebude fungovať)
```apache
Content-Security-Policy: default-src 'self'; 
script-src 'self'; 
style-src 'self'; 
img-src 'self' data:; 
font-src 'self'
```

#### 2. Moderate CSP (Povoľuje inline ale nie eval)
```apache
Content-Security-Policy: default-src 'self'; 
script-src 'self' 'unsafe-inline'; 
style-src 'self' 'unsafe-inline'; 
img-src 'self' data:; 
font-src 'self'
```

#### 3. Development CSP (Veľmi permisívne)
```apache
Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval'; 
img-src 'self' data: blob:; 
font-src 'self' data:
```

### CSP Odporúčania

**Pre production s Alpine.js:** Použiť súčasnú CSP s `'unsafe-eval'`
**Bez Alpine.js:** Použiť strict CSP pre maximálnu bezpečnosť

## 🔒 Apache Security Headers

### Security Headers v .htaccess

```apache
# XSS Protection
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set Referrer-Policy "strict-origin-when-cross-origin"

# Content Security Policy
Header always set Content-Security-Policy "default-src 'self'..."

# HSTS (pre HTTPS)
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

### Directory Protection

```apache
# Root .htaccess - Blokuje prístup k citlivým adresárom
<DirectoryMatch "/(config|src|vendor|themes|data)">
    Require all denied
</DirectoryMatch>

# Redirect na public/
RewriteRule ^(.*)$ public/$1 [L]
```

## 🛡️ Session Security

### Session Konfigurácia

```php
// config/autoload/session.global.php
return [
    'session' => [
        'cookie_name' => 'PHPSESSID',
        'cookie_httponly' => true,        // Zabráni XSS prístupu
        'cookie_samesite' => 'Lax',       // CSRF ochrana
        'cookie_secure' => true,          // Len HTTPS (production)
        'persistent' => true,
    ],
];
```

### Session Best Practices

```php
// Pri prihlásení - regeneruj session ID
session_regenerate_id(true);

// Pri odhlásení - zničí session
session_destroy();

// Kontrola session validity
if (!isset($_SESSION['user_id'])) {
    // Redirect na login
}
```

## 🔐 CSRF Protection

### CSRF Middleware

```php
// Automatické generovanie CSRF tokenov
<input type="hidden" name="csrf_token" value="<?= $escapeHtml($csrf_token ?? '') ?>">

// Validácia v middleware
$app->route('/protected-form', [
    \User\Middleware\CsrfMiddleware::class,
    'App\Handler\FormHandler'
], ['GET', 'POST']);
```

### CSRF Token Usage

```php
// V template
<?= $escapeHtml($csrf_token ?? '') ?>

// V JavaScript (ak potrebné)
<meta name="csrf-token" content="<?= $escapeHtml($csrf_token ?? '') ?>">
```

## 🗄️ Database Security

### Prepared Statements

```php
// ✅ BEZPEČNÉ - Prepared statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);

// ❌ NEBEZPEČNÉ - String concatenation
$query = "SELECT * FROM users WHERE username = '$username'";
```

### Database Isolation

```php
// Oddelené databázy pre rôzne účely
$userPdo = $container->get('pdo.user');    // user.db
$markPdo = $container->get('pdo.mark');    // mark.db
```

## 🔍 Security Testing

### Testovanie Path Traversal

```bash
# Spusti testy
composer test tests/Service/PathServiceTest.php

# Manuálne testovanie
curl "http://localhost:8080/themes/../../../etc/passwd"
# Očakávaný výsledok: 403 Forbidden
```

### Testovanie Security Headers

```bash
# Skontroluj security headers
curl -I https://your-domain.com

# Očakávané headers:
# X-Content-Type-Options: nosniff
# X-XSS-Protection: 1; mode=block
# X-Frame-Options: SAMEORIGIN
# Content-Security-Policy: default-src 'self'...
```

### Testovanie CSP

```bash
# Otvor browser console a skontroluj CSP errors
# Očakávané: Žiadne CSP violations pre normálne používanie
```

## 🚨 Security Monitoring

### Production Monitoring

1. **Logovanie pokusov o path traversal**
2. **Rate limiting** pre podozrivé požiadavky
3. **WAF pravidlá** pre dodatočnú ochranu
4. **Pravidelné bezpečnostné audity**

### Security Checklist

- [ ] PathService používaný pre všetky file operácie
- [ ] CSP headers správne nakonfigurované
- [ ] Apache .htaccess súbory na mieste
- [ ] Session security zapnuté
- [ ] CSRF protection aktívne
- [ ] Database prepared statements
- [ ] Security headers testované
- [ ] Directory protection overené

## 🔄 Security Updates

Pri pridávaní nových funkcií vždy:

1. Použite `PathService` pre validáciu ciest
2. Nikdy nepracujte priamo s `$_GET`, `$_POST` cestami
3. Testujte proti path traversal útokom
4. Aktualizujte CSP ak pridávate nové frameworky
5. Dokumentujte bezpečnostné opatrenia

---

## 📚 Súvisiace Dokumenty

### 🚀 Production a Deployment
- **[APACHE_GUIDE.md](APACHE_GUIDE.md)** - Apache konfigurácia a security headers
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production security a best practices
- **[CONFIGURATION.md](CONFIGURATION.md)** - Bezpečnostné konfigurácie

### 🏗️ Development a Architecture
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Security architektúra
- **[USER_MODULE.md](USER_MODULE.md)** - User authentication a authorization
- **[API_REFERENCE.md](API_REFERENCE.md)** - Security API dokumentácia

### 🔧 Údržba a Support
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - Riešenie bezpečnostných problémov
- **[MAINTENANCE.md](MAINTENANCE.md)** - Security monitoring
- **[QUICK_START.md](QUICK_START.md)** - Bezpečné spustenie

**Späť na hlavnú:** [README.md](README.md)
