# Apache Configuration for Mezzio Production

## .htaccess Files Overview

This project includes comprehensive `.htaccess` files for production security and performance:

### 1. Root `.htaccess`
- **Location**: `/.htaccess`
- **Purpose**: Deny access to all root files and redirect to public/
- **Features**:
  - Blocks access to config/, src/, vendor/, themes/
  - Redirects root requests to public/ directory
  - Security headers fallback

### 2. Public `.htaccess`
- **Location**: `/public/.htaccess`
- **Purpose**: Main application configuration
- **Features**:
  - **Security Headers**: XSS, CSRF, Clickjacking protection
  - **Caching**: Long-term cache for versioned assets (1 year)
  - **Compression**: Gzip compression for all text files
  - **URL Rewriting**: Routes all requests to index.php
  - **MIME Types**: Proper content types for web fonts and assets


## Apache Modules Required

Ensure these Apache modules are enabled:

```apache
# Required modules
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule headers_module modules/mod_headers.so
LoadModule expires_module modules/mod_expires.so
LoadModule deflate_module modules/mod_deflate.so
LoadModule mime_module modules/mod_mime.so
```

## Virtual Host Configuration

Recommended Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/mezzio-app/public
    
    # Security
    ServerTokens Prod
    ServerSignature Off
    
    # Enable .htaccess
    <Directory "/path/to/mezzio-app">
        AllowOverride All
        Require all granted
    </Directory>
    
    # Additional security
    <Directory "/path/to/mezzio-app/public">
        AllowOverride All
        Require all granted
    </Directory>
    
    # Block access to sensitive directories
    <DirectoryMatch "/(config|src|vendor|themes)">
        Require all denied
    </DirectoryMatch>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/mezzio_error.log
    CustomLog ${APACHE_LOG_DIR}/mezzio_access.log combined
</VirtualHost>
```

## Security Features

### Headers Applied
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy: default-src 'self'...`

### Caching Strategy
- **Versioned assets** (CSS/JS with hash): 1 year cache
- **Images**: 1 month cache
- **Fonts**: 1 year cache
- **Manifest files**: 1 day cache

### Compression
- All text-based files compressed with gzip
- 70-80% size reduction for better performance

## Testing

Test your configuration:

```bash
# Test security headers
curl -I https://your-domain.com

# Test compression
curl -H "Accept-Encoding: gzip" -I https://your-domain.com

# Test directory protection
curl -I https://your-domain.com/config/
curl -I https://your-domain.com/vendor/
```

## Production Checklist

- [ ] Apache modules enabled
- [ ] Virtual host configured
- [ ] .htaccess files in place
- [ ] Directory permissions set (755/644)
- [ ] Security headers working
- [ ] Compression enabled
- [ ] Caching headers set
- [ ] Directory access blocked
