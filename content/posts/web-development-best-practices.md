---
title: "Web Development Best Practices 2025"
slug: "web-development-best-practices"
excerpt: "Moderné best practices pre web development. Performance, security, accessibility a user experience."
content_type: "post"
status: "published"
featured: false
published_at: "2025-01-07T11:20:00Z"
created_at: "2025-01-07T11:20:00Z"
updated_at: "2025-01-07T11:20:00Z"
category: "Development"
tags: ["Web Development", "Best Practices", "Performance", "Security"]
author: "Development Team"
---

# Web Development Best Practices 2025

Moderný web development vyžaduje dodržiavanie najlepších praktík. Pozrime sa na kľúčové oblasti.

## Performance Optimization

### 1. Core Web Vitals
```javascript
// Meranie LCP
new PerformanceObserver((entryList) => {
    for (const entry of entryList.getEntries()) {
        console.log('LCP:', entry.startTime);
    }
}).observe({entryTypes: ['largest-contentful-paint']});
```

### 2. Image Optimization
```html
<!-- Responsive images -->
<picture>
    <source media="(min-width: 800px)" srcset="large.webp">
    <source media="(min-width: 400px)" srcset="medium.webp">
    <img src="small.webp" alt="Description" loading="lazy">
</picture>
```

### 3. Code Splitting
```javascript
// Dynamic imports
const LazyComponent = lazy(() => import('./LazyComponent'));

// Route-based splitting
const Home = lazy(() => import('./pages/Home'));
const About = lazy(() => import('./pages/About'));
```

## Security Best Practices

### 1. Content Security Policy
```html
<meta http-equiv="Content-Security-Policy" 
      content="default-src 'self'; script-src 'self' 'unsafe-inline';">
```

### 2. HTTPS Everywhere
```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 3. Input Validation
```php
// PHP validation
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// SQL injection prevention
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

## Accessibility (A11y)

### 1. Semantic HTML
```html
<main>
    <article>
        <header>
            <h1>Article Title</h1>
            <time datetime="2025-01-07">January 7, 2025</time>
        </header>
        <section>
            <p>Article content...</p>
        </section>
    </article>
</main>
```

### 2. ARIA Labels
```html
<button aria-label="Close dialog" aria-expanded="false">
    <span aria-hidden="true">&times;</span>
</button>

<nav aria-label="Main navigation">
    <ul role="menubar">
        <li role="menuitem"><a href="/">Home</a></li>
    </ul>
</nav>
```

### 3. Keyboard Navigation
```css
/* Focus indicators */
:focus-visible {
    outline: 2px solid #0066cc;
    outline-offset: 2px;
}

/* Skip links */
.skip-link {
    position: absolute;
    top: -40px;
    left: 6px;
    background: #000;
    color: #fff;
    padding: 8px;
    text-decoration: none;
    transition: top 0.3s;
}

.skip-link:focus {
    top: 6px;
}
```

## Modern CSS Practices

### 1. CSS Grid & Flexbox
```css
/* Grid layout */
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

/* Flexbox utilities */
.flex-center {
    display: flex;
    align-items: center;
    justify-content: center;
}
```

### 2. Custom Properties
```css
:root {
    --primary-color: #0066cc;
    --secondary-color: #6c757d;
    --font-size-base: 1rem;
    --spacing-unit: 0.5rem;
}

.button {
    background-color: var(--primary-color);
    padding: calc(var(--spacing-unit) * 2);
}
```

### 3. Container Queries
```css
@container (min-width: 400px) {
    .card {
        display: flex;
        flex-direction: row;
    }
}
```

## JavaScript Best Practices

### 1. Modern ES6+ Features
```javascript
// Destructuring
const { name, email } = user;

// Template literals
const message = `Hello, ${name}!`;

// Arrow functions
const users = data.map(item => ({
    id: item.id,
    name: item.name
}));

// Async/await
async function fetchData() {
    try {
        const response = await fetch('/api/data');
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error:', error);
    }
}
```

### 2. Error Handling
```javascript
// Global error handler
window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
    // Send to logging service
});

// Promise rejection handler
window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
});
```

### 3. Performance Monitoring
```javascript
// Performance API
const observer = new PerformanceObserver((list) => {
    for (const entry of list.getEntries()) {
        console.log(`${entry.name}: ${entry.duration}ms`);
    }
});

observer.observe({ entryTypes: ['measure', 'navigation'] });
```

## Testing Strategies

### 1. Unit Testing
```javascript
// Jest example
describe('Calculator', () => {
    test('adds 1 + 2 to equal 3', () => {
        expect(add(1, 2)).toBe(3);
    });
});
```

### 2. Integration Testing
```javascript
// Testing API endpoints
test('GET /api/users returns user list', async () => {
    const response = await request(app).get('/api/users');
    expect(response.status).toBe(200);
    expect(response.body).toHaveProperty('users');
});
```

### 3. E2E Testing
```javascript
// Playwright example
test('user can login', async ({ page }) => {
    await page.goto('/login');
    await page.fill('[name="email"]', 'user@example.com');
    await page.fill('[name="password"]', 'password');
    await page.click('[type="submit"]');
    await expect(page).toHaveURL('/dashboard');
});
```

## Development Workflow

### 1. Git Best Practices
```bash
# Conventional commits
git commit -m "feat: add user authentication"
git commit -m "fix: resolve login validation issue"
git commit -m "docs: update API documentation"

# Feature branches
git checkout -b feature/user-profile
git checkout -b fix/login-bug
```

### 2. Code Review Checklist
- ✅ Code follows style guidelines
- ✅ Tests are included and passing
- ✅ Documentation is updated
- ✅ Performance impact considered
- ✅ Security implications reviewed

### 3. CI/CD Pipeline
```yaml
# GitHub Actions example
name: CI/CD
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup Node.js
        uses: actions/setup-node@v2
        with:
          node-version: '18'
      - run: npm ci
      - run: npm test
      - run: npm run build
```

## Záver

Dodržiavanie best practices je kľúčové pre úspešný web development. Investujte čas do učenia sa a aplikovania týchto praktík.

### Ďalšie zdroje
- [Web.dev](https://web.dev) - Google's web development guides
- [MDN Web Docs](https://developer.mozilla.org) - Comprehensive documentation
- [A11y Project](https://www.a11yproject.com) - Accessibility resources

---

*Chcete sa dozvedieť viac? Sledujte naše ďalšie články o web developmente.*
