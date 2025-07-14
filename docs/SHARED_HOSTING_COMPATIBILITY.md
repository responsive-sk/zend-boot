# Shared Hosting Compatibility Guide

Tento dokument poskytuje riešenia pre nasadenie aplikácie na najslabších shared hosting prostrediach s obmedzenými funkciami.

## Identifikované problémy

### 1. Zakázané PHP funkcie
Mnohé shared hosting provideri zakazujú:
- `exec()`, `shell_exec()`, `system()`
- `proc_open()`, `popen()`
- `file_get_contents()` pre external URLs
- `curl_exec()` (niekedy)

### 2. Obmedzené permissions
- Nemožnosť meniť ownership (`chown`)
- Obmedzené `chmod` permissions
- Žiadny prístup mimo web root

### 3. Staré PHP verzie
- PHP 7.4 alebo starší
- Chýbajúce extensions
- Nízke memory_limit (64MB-128MB)

### 4. Obmedzený prístup
- Žiadny CLI/SSH prístup
- Len FTP/cPanel upload
- Žiadny composer install na serveri

## Riešenia

### 1. Fallback pre zakázané funkcie

#### A. Function availability checker

```php
// src/Core/src/Compatibility/FunctionChecker.php
class FunctionChecker
{
    private static array $disabledFunctions = [];
    
    public static function init(): void
    {
        $disabled = ini_get('disable_functions');
        self::$disabledFunctions = $disabled ? explode(',', $disabled) : [];
    }
    
    public static function isAvailable(string $function): bool
    {
        return function_exists($function) && 
               !in_array($function, self::$disabledFunctions, true);
    }
    
    public static function safeExec(string $command): ?string
    {
        if (self::isAvailable('exec')) {
            exec($command, $output);
            return implode("\n", $output);
        }
        
        if (self::isAvailable('shell_exec')) {
            return shell_exec($command);
        }
        
        if (self::isAvailable('system')) {
            ob_start();
            system($command);
            return ob_get_clean();
        }
        
        return null; // Žiadna exec funkcia dostupná
    }
}
```

#### B. Safe file operations

```php
// src/Core/src/Compatibility/SafeFileOperations.php
class SafeFileOperations
{
    public static function createDirectory(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }
        
        try {
            return mkdir($path, 0755, true);
        } catch (Exception $e) {
            // Fallback: try without recursive
            return @mkdir($path, 0755);
        }
    }
    
    public static function safeWrite(string $file, string $content): bool
    {
        try {
            return file_put_contents($file, $content, LOCK_EX) !== false;
        } catch (Exception $e) {
            // Fallback: try without lock
            return @file_put_contents($file, $content) !== false;
        }
    }
    
    public static function safeChmod(string $path, int $mode): bool
    {
        try {
            return chmod($path, $mode);
        } catch (Exception $e) {
            return false; // Ignore chmod errors on shared hosting
        }
    }
}
```

### 2. Memory-efficient initialization

```php
// src/Core/src/Compatibility/LowMemoryMode.php
class LowMemoryMode
{
    public static function isLowMemory(): bool
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return false; // Unlimited
        }
        
        $bytes = self::parseMemoryLimit($limit);
        return $bytes < 256 * 1024 * 1024; // Less than 256MB
    }
    
    public static function optimizeForLowMemory(): void
    {
        if (self::isLowMemory()) {
            // Disable opcache if memory is very low
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            
            // Force garbage collection
            gc_collect_cycles();
            
            // Reduce error reporting in production
            error_reporting(E_ERROR | E_WARNING);
        }
    }
    
    private static function parseMemoryLimit(string $limit): int
    {
        $unit = strtolower(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);
        
        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
```

### 3. PHP version compatibility

```php
// src/Core/src/Compatibility/PhpVersionCheck.php
class PhpVersionCheck
{
    public const MIN_VERSION = '7.4.0';
    public const RECOMMENDED_VERSION = '8.2.0';
    
    public static function check(): array
    {
        $current = PHP_VERSION;
        $issues = [];
        
        if (version_compare($current, self::MIN_VERSION, '<')) {
            $issues[] = "PHP {$current} is too old. Minimum: " . self::MIN_VERSION;
        }
        
        if (version_compare($current, self::RECOMMENDED_VERSION, '<')) {
            $issues[] = "PHP {$current} is outdated. Recommended: " . self::RECOMMENDED_VERSION;
        }
        
        // Check required extensions
        $required = ['json', 'mbstring', 'openssl'];
        foreach ($required as $ext) {
            if (!extension_loaded($ext)) {
                $issues[] = "Missing required extension: {$ext}";
            }
        }
        
        return $issues;
    }
    
    public static function isCompatible(): bool
    {
        return version_compare(PHP_VERSION, self::MIN_VERSION, '>=');
    }
}
```

### 4. Shared hosting deployment script

```php
// deploy-shared-hosting.php
class SharedHostingDeployer
{
    private string $buildDir = 'build-shared-hosting';
    
    public function deploy(): void
    {
        echo "Preparing for shared hosting deployment...\n";
        
        // 1. Check compatibility
        $this->checkCompatibility();
        
        // 2. Create minimal build
        $this->createMinimalBuild();
        
        // 3. Generate .htaccess with fallbacks
        $this->generateHtaccess();
        
        // 4. Create setup instructions
        $this->createSetupInstructions();
        
        echo "✅ Shared hosting package ready in: {$this->buildDir}/\n";
    }
    
    private function checkCompatibility(): void
    {
        $issues = PhpVersionCheck::check();
        if (!empty($issues)) {
            echo "⚠️  Compatibility issues found:\n";
            foreach ($issues as $issue) {
                echo "   - {$issue}\n";
            }
        }
    }
    
    private function createMinimalBuild(): void
    {
        // Copy only essential files
        $essential = [
            'public/',
            'config/',
            'src/',
            'var/',
            'vendor/',
            'composer.json',
            'composer.lock'
        ];
        
        foreach ($essential as $item) {
            if (file_exists($item)) {
                $this->copyRecursive($item, $this->buildDir . '/' . $item);
            }
        }
    }
    
    private function generateHtaccess(): void
    {
        $htaccess = <<<'HTACCESS'
# Shared Hosting .htaccess with fallbacks
RewriteEngine On

# Try modern rewrite first
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Fallback for older Apache
<IfModule !mod_rewrite.c>
    ErrorDocument 404 /index.php
</IfModule>

# Security headers (if supported)
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# PHP settings (if allowed)
<IfModule mod_php.c>
    php_value memory_limit 256M
    php_value max_execution_time 60
    php_value upload_max_filesize 10M
</IfModule>
HTACCESS;
        
        file_put_contents($this->buildDir . '/public/.htaccess', $htaccess);
    }
}
```

### 5. Automatic directory creation

```php
// public/index.php - Add at the beginning
// Auto-create var/ structure for shared hosting
$varDirs = ['var', 'var/data', 'var/cache', 'var/logs', 'var/tmp'];
foreach ($varDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}
```

### 6. Error handling for shared hosting

```php
// src/Core/src/Compatibility/SharedHostingErrorHandler.php
class SharedHostingErrorHandler
{
    public static function register(): void
    {
        // Graceful error handling
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError($severity, $message, $file, $line): bool
    {
        // Log to file instead of displaying
        $logFile = 'var/logs/php-errors.log';
        $entry = date('Y-m-d H:i:s') . " [{$severity}] {$message} in {$file}:{$line}\n";
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        
        return true; // Don't display errors
    }
    
    public static function handleException(Throwable $e): void
    {
        $logFile = 'var/logs/exceptions.log';
        $entry = date('Y-m-d H:i:s') . " " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n";
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        
        // Show user-friendly error
        http_response_code(500);
        echo "Application temporarily unavailable. Please try again later.";
    }
}
```

## Deployment checklist pre shared hosting

### Pre upload:
- [ ] Spusti `php deploy-shared-hosting.php`
- [ ] Skontroluj PHP version compatibility
- [ ] Over, že vendor/ je included
- [ ] Skontroluj file permissions

### Po uploade:
- [ ] Over, že var/ adresáre sa vytvorili
- [ ] Skontroluj .htaccess functionality
- [ ] Test základnej funkcionality
- [ ] Skontroluj error logs

### Troubleshooting:
- Ak nefunguje rewrite: skontroluj .htaccess
- Ak chýbajú permissions: ignoruj chmod errors
- Ak je málo pamäte: aktivuj LowMemoryMode
- Ak chýbajú funkcie: použij fallbacks

Tento guide zabezpečuje, že aplikácia bude fungovať aj na najslabších shared hosting prostrediach.
