# .htaccess Configuration Info

## Current Setup

### Active .htaccess Files
- **Root `.htaccess`**: Redirects to public/ and blocks access to sensitive directories
- **`public/.htaccess`**: Main application configuration with security headers, caching, and routing

### Removed .htaccess Files
The following .htaccess files were removed because they block PHP built-in server:
- `config/.htaccess` - Blocked access to config files
- `src/.htaccess` - Blocked access to source files  
- `vendor/.htaccess` - Blocked access to vendor files
- `themes/.htaccess` - Blocked access to theme files

## For Production Apache Server

If deploying to Apache server, you may want to restore these protective .htaccess files:

```bash
# Create protective .htaccess files for Apache
echo "Require all denied" > config/.htaccess
echo "Require all denied" > src/.htaccess  
echo "Require all denied" > vendor/.htaccess
echo "Require all denied" > themes/.htaccess
```

## PHP Built-in Server vs Apache

### PHP Built-in Server (Development)
- Reads .htaccess files and applies restrictions
- `Require all denied` blocks access completely
- Only root and public .htaccess needed

### Apache Server (Production)
- .htaccess files provide security layers
- Directory protection prevents direct access
- All .htaccess files recommended for security

## Security Notes

- **PHP built-in server**: Directories are not exposed by default
- **Apache server**: Directories need explicit protection via .htaccess
- **Production**: Always use directory protection .htaccess files
