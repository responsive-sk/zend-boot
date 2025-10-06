.
├── bin
│   ├── backup-databases.php
│   ├── build-dev.sh
│   ├── build-production.sh
│   ├── build-to-directory.sh
│   ├── cleanup-cache.php
│   ├── deploy.sh
│   ├── health-check.php
│   ├── init-all-db.php
│   ├── maintenance-db.php
│   ├── migrate-docs-to-content.php
│   ├── migrate-orbit-categories.php
│   ├── migrate-orbit-db.php
│   ├── migrate-to-hdm-paths.php
│   ├── monitor.sh
│   ├── orbit-db-helper.php
│   ├── shared-hosting-cleanup.php
│   ├── test-authorization.php
│   ├── test-mark-dashboard.php
│   ├── test-mark-login.php
│   ├── test-orbit-complete.php
│   ├── test-orbit-http.php
│   ├── test-orbit-integration.php
│   ├── test-orbit-mark.php
│   ├── test-orbit.php
│   ├── test-orbit-routes.php
│   └── test-template-renderer.php
├── config
│   ├── autoload
│   │   ├── authentication.global.php
│   │   ├── authorization.global.php
│   │   ├── database.global.php
│   │   ├── database.global.php.backup
│   │   ├── database.local.php.dist
│   │   ├── dependencies.global.php
│   │   ├── logging.local.php.dist
│   │   ├── session.global.php
│   │   ├── session.local.php.dist
│   │   ├── templates.global.php
│   │   ├── templates.global.php.backup
│   │   └── view_helpers.global.php
│   ├── routes
│   │   ├── app.php
│   │   ├── debug.php
│   │   ├── mark.php
│   │   ├── orbit.php
│   │   └── user.php
│   ├── config.php
│   ├── container.php
│   ├── development.config.php.dist
│   ├── paths.php
│   └── shared-hosting.php
├── content
│   ├── docs
│   │   ├── archive
│   │   │   ├── BIN_AUDIT_SUMMARY.md
│   │   │   ├── BIN_CLEANUP_COMPLETED.md
│   │   │   ├── BIN_CLEANUP_PLAN.md
│   │   │   ├── BIN_SCRIPTS_AUDIT.md
│   │   │   ├── CODE_QUALITY_AUDIT.md
│   │   │   ├── DEPENDENCIES_AUDIT.md
│   │   │   └── TEMPLATE.md
│   │   ├── en
│   │   │   ├── ACCESSIBILITY.md
│   │   │   ├── APACHE_GUIDE.md
│   │   │   ├── API_REFERENCE.md
│   │   │   ├── APPLICATION_FLOW_DIAGRAMS.md
│   │   │   ├── APP_PROTOCOL.md
│   │   │   ├── ARCHITECTURE.md
│   │   │   ├── CHANGELOG.md
│   │   │   ├── CONFIGURATION.md
│   │   │   ├── CRONTAB.md
│   │   │   ├── DEPLOYMENT.md
│   │   │   ├── DOCUMENTATION_STYLE_GUIDE.md
│   │   │   ├── INDEX.md
│   │   │   ├── MAINTENANCE.md
│   │   │   ├── QUICK_START.md
│   │   │   ├── SECURITY_GUIDE.md
│   │   │   ├── TODO.md
│   │   │   ├── TROUBLESHOOTING.md
│   │   │   └── USER_MODULE.md
│   │   └── sk
│   │       ├── ARCHITEKTURA.md
│   │       ├── BEZPECNOST.md
│   │       ├── DEPLOYMENT.md
│   │       ├── KONFIGURACIA.md
│   │       ├── README.md
│   │       ├── RIESENIE_PROBLEMOV.md
│   │       ├── RYCHLY_START.md
│   │       ├── SYNTAXTEST.md
│   │       └── USER_MODUL.md
│   ├── pages
│   │   ├── about.md
│   │   └── test-page.md
│   ├── posts
│   │   ├── 2025-01-12-orbit-cms-integration1.md
│   │   ├── 2025-01-12-orbit-cms-integration.md
│   │   ├── digital-marketing-strategies.md
│   │   ├── javascript-trends-2025.md
│   │   ├── orbit-cms-tutorial.md
│   │   ├── php82-features.md
│   │   └── web-development-best-practices.md
│   └── templates
│       ├── page.md
│       └── post.md
├── docs
│   ├── archive
│   │   ├── BIN_AUDIT_SUMMARY.md
│   │   ├── BIN_CLEANUP_COMPLETED.md
│   │   ├── BIN_CLEANUP_PLAN.md
│   │   ├── BIN_SCRIPTS_AUDIT.md
│   │   ├── CODE_QUALITY_AUDIT.md
│   │   ├── DEPENDENCIES_AUDIT.md
│   │   └── TEMPLATE.md
│   ├── sk
│   │   ├── ARCHITEKTURA.md
│   │   ├── BEZPECNOST.md
│   │   ├── DEPLOYMENT.md
│   │   ├── KONFIGURACIA.md
│   │   ├── README.md
│   │   ├── RIESENIE_PROBLEMOV.md
│   │   ├── RYCHLY_START.md
│   │   └── USER_MODUL.md
│   ├── ACCESSIBILITY.md
│   ├── APACHE_GUIDE.md
│   ├── API_REFERENCE.md
│   ├── APPLICATION_FLOW_DIAGRAMS.md
│   ├── APP_PROTOCOL.md
│   ├── ARCHITECTURE.md
│   ├── CHANGELOG.md
│   ├── CONFIGURATION.md
│   ├── CRONTAB.md
│   ├── DEPLOYMENT.md
│   ├── DOCUMENTATION_STYLE_GUIDE.md
│   ├── MAINTENANCE.md
│   ├── QUICK_START.md
│   ├── README.md
│   ├── SECURITY_GUIDE.md
│   ├── TODO.md
│   ├── TROUBLESHOOTING.md
│   └── USER_MODULE.md
├── modules
│   ├── Mark
│   │   ├── src
│   │   │   ├── Entity
│   │   │   │   └── MarkUser.php
│   │   │   ├── Handler
│   │   │   │   ├── Api
│   │   │   │   │   ├── HealthHandler.php
│   │   │   │   │   └── StatsHandler.php
│   │   │   │   ├── BackupCreateHandler.php
│   │   │   │   ├── BackupHandler.php
│   │   │   │   ├── CacheClearHandler.php
│   │   │   │   ├── CacheHandler.php
│   │   │   │   ├── DashboardHandlerFactory.php
│   │   │   │   ├── DashboardHandler.php
│   │   │   │   ├── DatabaseHandler.php
│   │   │   │   ├── HealthHandlerFactory.php
│   │   │   │   ├── HealthHandler.php
│   │   │   │   ├── LoginHandlerFactory.php
│   │   │   │   ├── LoginHandler.php
│   │   │   │   ├── LogoutHandlerFactory.php
│   │   │   │   ├── LogoutHandler.php
│   │   │   │   ├── LogsHandler.php
│   │   │   │   ├── MarkCreateHandler.php
│   │   │   │   ├── MarkDeleteHandler.php
│   │   │   │   ├── MarkEditHandler.php
│   │   │   │   ├── MarkManagementHandler.php
│   │   │   │   ├── SettingsHandler.php
│   │   │   │   ├── UserDeleteHandler.php
│   │   │   │   ├── UserEditHandler.php
│   │   │   │   └── UserManagementHandler.php
│   │   │   ├── Middleware
│   │   │   │   ├── MarkAuthenticationMiddlewareFactory.php
│   │   │   │   ├── MarkAuthenticationMiddleware.php
│   │   │   │   ├── SupermarkAuthorizationMiddlewareFactory.php
│   │   │   │   └── SupermarkAuthorizationMiddleware.php
│   │   │   ├── Service
│   │   │   │   ├── MarkUserRepositoryFactory.php
│   │   │   │   ├── MarkUserRepository.php
│   │   │   │   ├── SystemStatsServiceFactory.php
│   │   │   │   └── SystemStatsService.php
│   │   │   └── ConfigProvider.php
│   │   └── templates
│   │       └── mark
│   │           ├── dashboard.phtml
│   │           ├── health.phtml
│   │           ├── login-old.phtml
│   │           └── login.phtml
│   ├── Orbit
│   │   ├── src
│   │   │   ├── Entity
│   │   │   │   ├── Category.php
│   │   │   │   ├── Content.php
│   │   │   │   └── Tag.php
│   │   │   ├── Factory
│   │   │   │   ├── ApiSearchHandlerFactory.php
│   │   │   │   ├── BlogHandlerFactory.php
│   │   │   │   ├── BlogTailwindHandlerFactory.php
│   │   │   │   ├── CategoryRepositoryFactory.php
│   │   │   │   ├── ContentRepositoryFactory.php
│   │   │   │   ├── DocsHandlerFactory.php
│   │   │   │   ├── JsonDriverFactory.php
│   │   │   │   ├── MarkContentHandlerFactory.php
│   │   │   │   ├── MarkDashboardHandlerFactory.php
│   │   │   │   ├── MarkdownDriverFactory.php
│   │   │   │   ├── MarkEditorHandlerFactory.php
│   │   │   │   ├── OrbitManagerFactory.php
│   │   │   │   ├── PageHandlerFactory.php
│   │   │   │   ├── PostHandlerFactory.php
│   │   │   │   ├── SearchServiceFactory.php
│   │   │   │   └── TagRepositoryFactory.php
│   │   │   ├── Handler
│   │   │   │   ├── ApiSearchHandler.php
│   │   │   │   ├── BlogHandler.php
│   │   │   │   ├── BlogTailwindHandler.php
│   │   │   │   ├── DocsHandler.php
│   │   │   │   ├── MarkContentHandler.php
│   │   │   │   ├── MarkDashboardHandler.php
│   │   │   │   ├── MarkEditorHandler.php
│   │   │   │   ├── PageHandler.php
│   │   │   │   └── PostHandler.php
│   │   │   ├── Service
│   │   │   │   ├── FileDriver
│   │   │   │   │   ├── FileDriverInterface.php
│   │   │   │   │   ├── JsonDriver.php
│   │   │   │   │   └── MarkdownDriver.php
│   │   │   │   ├── CategoryRepository.php
│   │   │   │   ├── ContentRepository.php
│   │   │   │   ├── OrbitManager.php
│   │   │   │   ├── SearchService.php
│   │   │   │   └── TagRepository.php
│   │   │   └── ConfigProvider.php
│   │   └── templates
│   │       └── orbit
│   │           ├── blog
│   │           │   ├── index.phtml
│   │           │   └── index-tailwind.phtml
│   │           ├── docs
│   │           │   ├── index.phtml
│   │           │   └── view.phtml
│   │           ├── mark
│   │           │   ├── content
│   │           │   │   ├── create.phtml
│   │           │   │   ├── edit.phtml
│   │           │   │   └── index.phtml
│   │           │   ├── dashboard.phtml
│   │           │   └── editor.phtml
│   │           ├── page
│   │           │   └── view.phtml
│   │           └── post
│   │               ├── view.phtml
│   │               ├── view-tailwind.phtml
│   │               └── view-test.phtml
│   └── User
│       ├── src
│       │   ├── Entity
│       │   │   └── User.php
│       │   ├── Form
│       │   │   ├── LoginForm.php
│       │   │   └── RegistrationForm.php
│       │   ├── Handler
│       │   │   ├── AdminHandlerFactory.php
│       │   │   ├── AdminHandler.php
│       │   │   ├── DashboardHandlerFactory.php
│       │   │   ├── DashboardHandler.php
│       │   │   ├── DashboardHandler.php.backup
│       │   │   ├── LoginHandlerFactory.php
│       │   │   ├── LoginHandler.php
│       │   │   ├── LogoutHandler.php
│       │   │   ├── RegistrationHandlerFactory.php
│       │   │   ├── RegistrationHandler.php
│       │   │   ├── SimpleDashboardHandler.php
│       │   │   ├── SimpleLoginHandlerFactory.php
│       │   │   ├── SimpleLoginHandler.php
│       │   │   └── SimpleLogoutHandler.php
│       │   ├── Middleware
│       │   │   ├── CsrfMiddlewareFactory.php
│       │   │   ├── CsrfMiddleware.php
│       │   │   ├── RequireLoginMiddlewareFactory.php
│       │   │   ├── RequireLoginMiddleware.php
│       │   │   ├── RequireRoleMiddlewareFactory.php
│       │   │   └── RequireRoleMiddleware.php
│       │   ├── Service
│       │   │   ├── AuthenticatedUser.php
│       │   │   ├── AuthenticationServiceFactory.php
│       │   │   ├── AuthenticationService.php
│       │   │   ├── MezzioUserRepository.php
│       │   │   ├── SimpleAuthenticationFactory.php
│       │   │   ├── SimpleAuthentication.php
│       │   │   ├── UserRepositoryFactory.php
│       │   │   └── UserRepository.php
│       │   └── ConfigProvider.php
│       └── templates
│           └── user
│               ├── dashboard.phtml
│               ├── login-old.phtml
│               ├── login.phtml
│               ├── register.phtml
│               ├── simple-dashboard.phtml
│               └── simple-login.phtml
├── src
│   ├── Boot
│   │   ├── ApplicationBootstrap.php
│   │   ├── ApplicationFactory.php
│   │   ├── ContainerBuilder.php
│   │   └── EnvironmentHandler.php
│   ├── Database
│   │   ├── MarkMigration.php
│   │   ├── MigrationServiceFactory.php
│   │   ├── MigrationService.php
│   │   ├── PdoFactory.php
│   │   ├── SystemMigration.php
│   │   └── UserMigration.php
│   ├── Factory
│   │   ├── DatabaseConfigFactory.php
│   │   └── TemplateConfigFactory.php
│   ├── Handler
│   │   ├── BootstrapDemoHandlerFactory.php
│   │   ├── BootstrapDemoHandler.php
│   │   ├── DebugHandler.php
│   │   ├── HomeHandlerFactory.php
│   │   ├── HomeHandler.php
│   │   ├── MainDemoHandlerFactory.php
│   │   ├── MainDemoHandler.php
│   │   ├── TemplateHandlerFactory.php
│   │   └── TemplateHandler.php
│   ├── Helper
│   │   ├── AssetHelperFactory.php
│   │   └── AssetHelper.php
│   ├── Service
│   │   ├── Factory
│   │   │   └── PathsServiceFactory.php
│   │   ├── Preset
│   │   │   └── MezzioOrbitPreset.php
│   │   ├── PathServiceInterface.php
│   │   ├── UnifiedPathServiceFactory.php
│   │   └── UnifiedPathService.php
│   ├── Session
│   │   └── SimpleSessionPersistence.php
│   └── Template
│       ├── FormHelper.php
│       ├── PhpRendererFactory.php
│       └── PhpRenderer.php
├── templates
│   ├── app
│   │   └── home.phtml
│   ├── error
│   │   └── 404.phtml
│   ├── layout
│   │   ├── default.phtml
│   │   ├── home.phtml
│   │   └── orbit-main.phtml
│   └── themes
│       ├── bootstrap
│       │   ├── src
│       │   │   ├── main.js
│       │   │   └── style.css
│       │   ├── package.json
│       │   └── vite.config.js
│       └── main
│           ├── src
│           │   ├── images
│           │   │   ├── icons
│           │   │   │   ├── checking.svg
│           │   │   │   ├── done.svg
│           │   │   │   ├── play.svg
│           │   │   │   ├── progress.svg
│           │   │   │   ├── telegram.svg
│           │   │   │   ├── time-forward.svg
│           │   │   │   ├── time.svg
│           │   │   │   └── youtube.svg
│           │   │   ├── nav
│           │   │   │   ├── logo.svg
│           │   │   │   └── logo-svgo.svg
│           │   │   ├── apple-touch-icon.png
│           │   │   ├── checking.svg
│           │   │   ├── digital-marketing.jpg
│           │   │   ├── done.svg
│           │   │   ├── favicon-32x32.png
│           │   │   ├── favicon.ico
│           │   │   ├── javascript.jpg
│           │   │   ├── php82.jpg
│           │   │   ├── play.svg
│           │   │   ├── progress.svg
│           │   │   ├── telegram.svg
│           │   │   ├── time-forward.svg
│           │   │   ├── time.svg
│           │   │   ├── web-dev.jpg
│           │   │   ├── welcome.jpg
│           │   │   └── youtube.svg
│           │   ├── main.js
│           │   └── style.css
│           ├── package.json
│           ├── postcss.config.js
│           ├── tailwind.config.js
│           └── vite.config.js
├── tests
│   ├── _output
│   │   └── report.xml
│   └── Unit
│       ├── Service
│       │   └── UnifiedPathServiceTest.php
│       └── Template
│           └── PhpRendererTest.php
├── var
│   ├── cache
│   ├── logs
│   ├── sessions
│   ├── storage
│   │   ├── mark.db
│   │   ├── orbit.db
│   │   ├── system.db
│   │   └── user.db
│   └── uploads
├── clover.xml
├── composer.json
├── composer.lock
├── coverage.txt
├── coverage.xml
├── directory_tree.md
├── .gitignore
├── .htaccess
├── phpcs.xml
├── phpstan.neon
├── phpunit.xml
└── rector.php

84 directories, 344 files
