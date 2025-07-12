# * Bezpečnostný Návod

Kompletný bezpečnostný návod pre Mezzio Minimal aplikáciu.

## 📋 Prehľad Bezpečnostných Opatrení

Aplikácia implementuje viacvrstvovú bezpečnosť:

1. **Path Traversal Protection** - PathService a Flysystem
2. **Content Security Policy** - CSP headers pre XSS ochranu
3. **Apache Security** - .htaccess konfigurácia
4. **Session Security** - Secure session handling
5. **CSRF Protection** - Token-based form protection
6. **Database Security** - Prepared statements, isolation

##  Path Traversal Protection

### PathService Implementation

Centralizovaná služba pre bezpečnú prácu s cestami:

```php
// - BEZPEČNÉ - Použitie PathService
$pathService = $container->get(PathService::class);
$safePath = $pathService->getPublicFilePath('user/avatar.jpg');

// - NEBEZPEČNÉ - Priama manipulácia ciest
$path = '../storage/database.db';
```

### Validácia Ciest

PathService automaticky:
- **Odstráni nebezpečné znaky**: `..`, `\`, `<>:"|?*`
- **Realpath validácia**: Kontrola, či finálna cesta je v rámci povoleného adresára
- **Normalizácia ciest**: Automatické čistenie a formátovanie

### AssetHelper Security

```php
// Automaticky validované cesty v AssetHelper
$assetHelper = $container->get(AssetHelper::class);
$cssUrl = $assetHelper->css('bootstrap');  // - Bezpečné
$jsUrl = $assetHelper->js('main');         // - Bezpečné
$imageUrl = $assetHelper->image('main', 'logo'); // - Bezpečné
```

### Zakázané Vzory

Tieto vzory sú automaticky blokované:
- `../../../etc/passwd`
- `folder/../../../sensitive.txt`
- `folder\\..\\..\sensitive.txt`
- `file<script>.txt`
- Akékoľvek cesty s `..` segmentmi

##  Content Security Policy (CSP)

### Súčasná CSP Konfigurácia

```apache
Content-Security-Policy: base-uri 'self';
default-src 'self';
script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net;
style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;
img-src 'self' data: blob: https://picsum.photos;
font-src 'self' https://cdn.jsdelivr.net;
connect-src 'self';
object-src 'none';
frame-ancestors 'none';
```

### Prečo 'unsafe-inline' a 'unsafe-eval'?

- **Bootstrap**: Vyžaduje inline styles pre komponenty
- **Alpine.js**: Potrebuje eval pre reactive expressions
- **Vite**: Development server používa inline scripts

### Produkčná CSP (Stricter)

```apache
# Pre production môžeš použiť prísnejšiu CSP
Content-Security-Policy: 
    default-src 'self';
    script-src 'self' 'nonce-{random}';
    style-src 'self' 'nonce-{random}';
    img-src 'self' data:;
    font-src 'self';
```

##  Apache Security (.htaccess)

### Directory Protection

```apache
# Blokuj prístup k citlivým adresárom
<FilesMatch "\.(db|sqlite|log|env)$">
    Require all denied
</FilesMatch>

# Blokuj prístup k config súborom
<DirectoryMatch "/(config|data|var|vendor)">
    Require all denied
</DirectoryMatch>
```

### Security Headers

```apache
# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

### Gzip Compression

```apache
# Kompresia pre performance
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE text/html
</IfModule>
```

##  Session Security

### Development Settings

```php
// config/autoload/session.global.php
'session' => [
    'cookie_name' => 'PHPSESSID',
    'cookie_httponly' => true,        // XSS protection
    'cookie_samesite' => 'Lax',       // CSRF protection
    'cookie_secure' => false,         // Set true for HTTPS
    'cache_expire' => 180,            // 3 hours
],
```

### Production Settings

```php
// config/autoload/session.local.php (production)
'session' => [
    'cookie_secure' => true,          // HTTPS only
    'cookie_samesite' => 'Strict',    // Stricter CSRF protection
    'cache_expire' => 60,             // 1 hour
    'gc_maxlifetime' => 3600,         // 1 hour
],
```

### Session Regeneration

```php
// Po prihlásení regeneruj session ID
session_regenerate_id(true);
```

##  CSRF Protection

### Token Generation

```php
// V handleroch
$csrfToken = $this->csrfGuard->generateToken();
$templateData['csrfToken'] = $csrfToken;
```

### Token Validation

```php
// V POST handleroch
$data = $request->getParsedBody();
if (!$this->csrfGuard->validateToken($data['_token'] ?? '')) {
    throw new InvalidArgumentException('Invalid CSRF token');
}
```

### Template Usage

```php
<!-- V formulároch -->
<input type="hidden" name="_token" value="<?= $csrfToken ?>" />
```

##  Database Security

### Prepared Statements

```php
// - BEZPEČNÉ - Prepared statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
$stmt->execute([$username]);

// - NEBEZPEČNÉ - String concatenation
$query = "SELECT * FROM users WHERE username = '$username'";
```

### Database Isolation

```php
// Oddelené databázy pre rôzne moduly
'database' => [
    'user' => ['dsn' => 'sqlite:data/user.db'],
    'mark' => ['dsn' => 'sqlite:data/mark.db'],
    'system' => ['dsn' => 'sqlite:data/system.db'],
],
```

### Password Hashing

```php
// Bezpečné hashovanie hesiel
public function setPassword(string $password): void
{
    $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
}

public function verifyPassword(string $password): bool
{
    return password_verify($password, $this->passwordHash);
}
```

##  Production Security Checklist

###  Server Configuration

- [ ] **HTTPS enabled** - SSL/TLS certifikát
- [ ] **PHP version** - PHP 8.3+ s najnovšími security patches
- [ ] **Web server** - Apache/Nginx s security headers
- [ ] **File permissions** - 755 pre adresáre, 644 pre súbory
- [ ] **Database access** - Restricted database user permissions

###  Application Security

- [ ] **Environment variables** - Sensitive data v .env súboroch
- [ ] **Debug mode disabled** - `DEBUG=false` v production
- [ ] **Error reporting** - Logs namiesto zobrazenia chýb
- [ ] **Session security** - Secure cookies, HTTPS only
- [ ] **CSRF protection** - Enabled pre všetky formuláre

###  File System Security

- [ ] **Directory protection** - .htaccess rules
- [ ] **Upload validation** - File type a size limits
- [ ] **Path traversal** - PathService implementation
- [ ] **Backup security** - Encrypted database backups

##  Security Monitoring

### Log Monitoring

```bash
# Sleduj security logy
tail -f var/logs/security.log

# Hľadaj podozrivé aktivity
grep "CSRF\|Path traversal\|Failed login" var/logs/app.log
```

### Health Checks

```bash
# Spusti security health check
php bin/health-check.php --security

# Kontrola file permissions
find . -type f -perm 777 -ls
```

---

**Ďalšie informácie:**
- [USER_MODUL.md](USER_MODUL.md) - User authentication security
- [KONFIGURACIA.md](KONFIGURACIA.md) - Security configuration
- [../APACHE_GUIDE.md](../APACHE_GUIDE.md) - Apache security setup
- [RIESENIE_PROBLEMOV.md](RIESENIE_PROBLEMOV.md) - Security troubleshooting
