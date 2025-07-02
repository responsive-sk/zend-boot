# Content Security Policy Options

## Current CSP (Alpine.js + Bootstrap + Lorem Picsum Compatible)
```
Content-Security-Policy: base-uri 'self'; default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https://picsum.photos; font-src 'self'; frame-ancestors 'none'; upgrade-insecure-requests
```

## Alternative CSP Options

### 1. Strict CSP (Most Secure - Alpine.js won't work)
```
Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; font-src 'self'
```

### 2. Moderate CSP (Allows inline styles/scripts but no eval)
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'
```

### 3. Alpine.js + Bootstrap Compatible (Current - allows eval + blob URLs)
```
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'
```

### 4. Development CSP (Very Permissive)
```
Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src 'self' data: blob:; font-src 'self' data:
```

## Framework Requirements

### Alpine.js Requirements
Alpine.js needs `'unsafe-eval'` because it:
- Compiles reactive expressions like `x-data="{ open: false }"`
- Uses `new Function()` for expression evaluation
- Creates dynamic reactive bindings

### Bootstrap Requirements
Bootstrap needs `blob:` in `img-src` because it:
- May generate blob URLs for dynamic images
- Uses blob URLs for certain components
- Creates temporary object URLs for image processing

### Lorem Picsum Requirements
Lorem Picsum needs `https://picsum.photos` in `img-src` because it:
- Provides placeholder images from external domain
- Used for portfolio showcase and demo content
- Requires external domain access for image loading

## Security vs Functionality Trade-off

- **With `'unsafe-eval'`**: Alpine.js works fully, slightly less secure
- **Without `'unsafe-eval'`**: More secure, but Alpine.js reactive features break

## Recommendation

For production with Alpine.js, use the current CSP with `'unsafe-eval'`.
If you don't need Alpine.js reactive features, use the strict CSP.
