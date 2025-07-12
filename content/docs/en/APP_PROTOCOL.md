# 🚀 HDM Boot Protocol Declaration

**Version:** 1.0  
**Status:** ACTIVE PROTOCOL  
**Effective Date:** 2025-06-24  

## 📜 PROTOCOL OVERVIEW

**HDM Boot Protocol** is a comprehensive architectural specification for building enterprise-grade PHP applications using **Hexagonal Architecture**, **Domain-Driven Design**, and **Modern Modular Architecture** principles.

This protocol defines mandatory standards, patterns, and practices that ensure:
- **Security-first development**
- **Scalable modular architecture** 
- **Production-ready deployment**
- **Cross-platform compatibility**

## 🏛️ CORE ARCHITECTURAL PILLARS

### **PILLAR I: Three-Database Foundation**
```
mark.db    → Mark system (administrative functionality)
user.db    → User system (application users)
system.db  → Core system modules (Cache, System logs, Template cache)
```

**MANDATORY:** All applications MUST implement three foundational databases with strict isolation.

**OPTIONAL:** Additional databases for optional modules (blog.db, orders.db, products.db, etc.)

**CONSERVATIVE APPROACH:** Three databases are sufficient for most applications. Additional databases should only be added when there is a clear business need, not preventively.

### **PILLAR II: Forbidden Terminology**
```
❌ "admin" → ✅ "mark"
❌ "administrator" → ✅ "mark user"
❌ "admin panel" → ✅ "mark system"
```

**MANDATORY:** The term "admin" is BANNED across all code, documentation, and interfaces.

### **PILLAR III: Secure Path Resolution**
```php
// ✅ PROTOCOL COMPLIANT - Use specific methods for configured paths
$databasePath = $this->paths->storage('mark.db');
$logPath = $this->paths->logs('app.log');
$cachePath = $this->paths->cache('templates');

// ⚠️ LIMITED USE - Generic path method (uses basePath + relativePath)
$genericPath = $this->paths->path('custom/file.txt');

// ❌ PROTOCOL VIOLATION
$path = '../storage/database.db';
```

**MANDATORY:** All file system operations MUST use ResponsiveSk\Slim4Paths service.
**RECOMMENDED:** Use specific methods (storage(), logs(), cache()) for configured paths instead of generic path() method.

### **PILLAR IV: Centralized Permission Management**
```php
// ✅ PROTOCOL COMPLIANT
$permissionManager->createDirectory('var/logs', 0755);

// ❌ PROTOCOL VIOLATION
mkdir('var/logs', 0777);
```

**MANDATORY:** All permission operations MUST use PermissionManager.

### **PILLAR V: Container Abstraction**
```php
// ✅ PROTOCOL COMPLIANT - Client can choose DI container
$container = ContainerFactory::create('symfony'); // or 'laravel', 'slim4'
$container->registerCoreServices();

// ❌ PROTOCOL VIOLATION - Hard-coded container dependency
$container = new \DI\Container(); // Locked to specific implementation
```

**MANDATORY:** All applications MUST use AbstractContainer for DI container abstraction.

### **PILLAR VI: Organized Directory Structure**
```
var/                  # Runtime data directory
├── storage/         # Database files (RECOMMENDED LOCATION)
│   ├── mark.db     # Mark system database
│   ├── user.db     # User system database
│   └── system.db   # Core system database
├── logs/           # Application logs
├── cache/          # Cache files
└── sessions/       # Session data

content/            # Content files (Git-friendly)
├── articles/       # Markdown articles
└── docs/          # Documentation

public/             # Web-accessible files only
├── assets/        # CSS, JS, images
└── media/         # User uploads
```

**RECOMMENDED:** Store databases in `var/storage/` instead of root `storage/` for better organization.
**MANDATORY:** Protect sensitive directories with `.htaccess` files.

## 🔧 IMPLEMENTATION STANDARDS

### **Database Managers**
```php
// ✅ PROTOCOL COMPLIANT
class MarkSqliteDatabaseManager extends AbstractDatabaseManager
{
    public function __construct(?string $databasePath = null, ?Paths $paths = null)
    {
        $paths = $paths ?? new Paths(__DIR__ . '/../../..');
        $databasePath = $databasePath ?? $paths->storage('mark.db'); // Use storage() method
        parent::__construct($databasePath, [], $paths);
    }

    protected function createConnection(): PDO { /* Implementation */ }
    protected function initializeDatabase(): void { /* Schema */ }
}

// ❌ PROTOCOL VIOLATION
class AdminDatabaseManager
{
    private PDO $connection; // No inheritance, wrong naming
}

// ❌ INCORRECT PATH USAGE
$dbPath = $paths->path('storage/mark.db'); // Uses basePath + relativePath

// ✅ CORRECT PATH USAGE
$dbPath = $paths->storage('mark.db'); // Uses configured storage path
```

### **Permission Modes**
```bash
# Production (strict permissions)
PERMISSIONS_STRICT=true  # 755/644

# Shared hosting (relaxed permissions)  
PERMISSIONS_STRICT=false # 777/666
```

### **Module Structure**
```
src/
├── Modules/
│   ├── Mark/           # Mark system modules
│   ├── Core/User/      # User system modules  
│   └── Core/App/       # Core application modules
└── SharedKernel/       # Cross-cutting concerns
```

## 🛡️ SECURITY REQUIREMENTS

### **Path Security**
- ✅ **Path traversal protection** via Paths service
- ✅ **Secure directory creation** with proper permissions
- ✅ **File access validation** before operations

### **Database Security**
- ✅ **Connection isolation** per database type
- ✅ **Prepared statements** for all queries
- ✅ **WAL mode** for SQLite concurrency

### **Permission Security**
- ✅ **Strict permissions** by default (755/644)
- ✅ **Environment-specific** permission modes
- ✅ **Centralized management** via PermissionManager

### **Container Security**
- ✅ **Container abstraction** via AbstractContainer
- ✅ **Type-safe service resolution** with getTyped()
- ✅ **Dependency injection** with auto-wiring support
- ✅ **Client choice** of DI container implementation

## 📋 COMPLIANCE CHECKLIST

### **Architecture Compliance**
- [ ] Three separate databases implemented (mark.db, user.db, system.db)
- [ ] Zero "admin" terminology in codebase
- [ ] All database managers extend AbstractDatabaseManager
- [ ] DatabaseManagerFactory used for all database access
- [ ] Secure path resolution via Paths service
- [ ] Organized directory structure (var/storage/, var/logs/, var/cache/)
- [ ] Proper Paths service method usage (storage(), logs(), cache() vs path())

### **Security Compliance**
- [ ] PermissionManager used for all file operations
- [ ] Strict permissions enabled by default
- [ ] No direct file system access outside PermissionManager
- [ ] Path traversal protection active
- [ ] Database connections properly isolated

### **Paths Service Compliance**
- [ ] Use storage() method for database files
- [ ] Use logs() method for log files
- [ ] Use cache() method for cache files
- [ ] Use sessions() method for session files
- [ ] Avoid generic path() method for configured paths
- [ ] Custom config/paths.php properly configured
- [ ] PathsFactory used for singleton instance management

### **Development Tools**
- [ ] Permission management scripts available
- [ ] Database health checking implemented
- [ ] Environment configuration documented
- [ ] Production deployment procedures defined
- [ ] Paths audit tool for hardcoded path detection

## 🚨 PROTOCOL VIOLATIONS

### **IMMEDIATE REJECTION**
Code violating these standards will be **immediately rejected**:

1. ❌ Using "admin" terminology
2. ❌ Direct path manipulation
3. ❌ Manual permission setting
4. ❌ Cross-database access without service layer
5. ❌ Database managers not extending AbstractDatabaseManager

### **ENFORCEMENT**
- **Code reviews** MUST verify protocol compliance
- **Automated checks** SHOULD be implemented where possible
- **Documentation** MUST reflect protocol standards
- **Training** MUST cover protocol requirements

## 🎯 PROTOCOL BENEFITS

### **For Developers**
- ✅ **Clear standards** - No ambiguity in implementation
- ✅ **Security by default** - Built-in protection mechanisms
- ✅ **Scalable architecture** - Modular, maintainable code
- ✅ **Production-ready** - Enterprise-grade patterns

### **For Organizations**
- ✅ **Reduced security risks** - Standardized security practices
- ✅ **Faster development** - Proven patterns and tools
- ✅ **Lower maintenance costs** - Consistent architecture
- ✅ **Cross-platform deployment** - Works everywhere

### **For Operations**
- ✅ **Predictable deployments** - Standardized procedures
- ✅ **Environment flexibility** - Strict/relaxed permission modes
- ✅ **Monitoring capabilities** - Built-in health checking
- ✅ **Troubleshooting tools** - Comprehensive diagnostics

## 🔄 PROTOCOL EVOLUTION

### **Version Control**
- **Major versions** require architectural review
- **Minor versions** for feature additions
- **Patch versions** for clarifications and fixes

### **Backward Compatibility**
- **Breaking changes** only in major versions
- **Deprecation warnings** before removal
- **Migration guides** for version upgrades

### **Community Input**
- **RFC process** for major changes
- **Public discussion** for controversial decisions
- **Implementation feedback** drives improvements

## 📞 PROTOCOL SUPPORT

### **Documentation**
- **CORE_ARCHITECTURE_PRINCIPLES.md** - Immutable foundation
- **Implementation guides** - Step-by-step procedures
- **Best practices** - Recommended patterns
- **Troubleshooting** - Common issues and solutions

### **Tools**
- **bin/fix-permissions.php** - Permission management
- **DatabaseManagerFactory** - Database abstraction
- **PermissionManager** - File system operations
- **ContainerFactory** - DI container abstraction
- **Health checking** - System diagnostics

### **Container Configuration**
```php
// Auto-detect available container
$container = ContainerFactory::createAuto();

// Specific container type
$container = ContainerFactory::create('symfony', [
    'definitions' => $customDefinitions,
    'compilation_path' => '/tmp/container'
]);

// Custom client container
$container = ContainerFactory::create('custom', [
    'class' => MyCustomContainer::class,
    'constructor_args' => [$config]
]);

// Register HDM Boot core services
$container->registerCoreServices();
```

---

## 📚 Súvisiace Dokumenty

### 🏗️ Implementácia a Architecture
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Implementácia HDM Boot Protocol
- **[USER_MODULE.md](USER_MODULE.md)** - User modul podľa HDM štandardov
- **[API_REFERENCE.md](API_REFERENCE.md)** - API implementácia

### 🚀 Production a Deployment
- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Production deployment podľa HDM
- **[SECURITY_GUIDE.md](SECURITY_GUIDE.md)** - HDM security štandardy
- **[CRONTAB.md](CRONTAB.md)** - HDM maintenance protokol

### 🔧 Konfigurácia a Support
- **[CONFIGURATION.md](CONFIGURATION.md)** - HDM konfiguračné štandardy
- **[MAINTENANCE.md](MAINTENANCE.md)** - HDM monitoring protokol
- **[TROUBLESHOOTING.md](TROUBLESHOOTING.md)** - HDM troubleshooting

**Späť na hlavnú:** [README.md](README.md)

---

**HDM Boot Protocol v1.0**
**Effective: 2025-06-24**
**Next Review: 2025-12-24**

**This protocol is ACTIVE and MANDATORY for all HDM Boot implementations.**
