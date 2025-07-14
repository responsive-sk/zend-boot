# Dark Theme Implementation

Aplikácia teraz podporuje dark theme s elegantným prepínačom v navigácii.

## Funkcie

### ✨ **Automatické prepínanie**
- **Toggle switch** v navigačnej lište
- **Lokálne uloženie** preferencie používateľa
- **Systémová detekcia** (prefers-color-scheme)
- **Klávesová skratka**: `Ctrl/Cmd + Shift + T`

### 🎨 **Vizuálne prvky**
- **Plynulé prechody** medzi témami (0.3s)
- **Notifikácie** pri zmene témy
- **Responzívny design** pre mobile zariadenia
- **Emoji ikony** (☀️/🌙) v toggle switchi

### 🔧 **Technické detaily**

#### CSS Variables
```scss
:root {
  --bs-body-bg: #ffffff;
  --bs-body-color: #212529;
  --bs-navbar-bg: #f8f9fa;
  // ... ďalšie premenné
}

[data-bs-theme="dark"] {
  --bs-body-bg: #121212;
  --bs-body-color: #e0e0e0;
  --bs-navbar-bg: #1e1e1e;
  // ... ďalšie premenné
}
```

#### JavaScript API
```javascript
// Získanie aktuálnej témy
ThemeUtils.getCurrentTheme(); // 'light' alebo 'dark'

// Kontrola dark theme
ThemeUtils.isDarkTheme(); // true/false

// Programatické prepnutie
ThemeUtils.toggleTheme();

// Event listener pre zmeny témy
window.addEventListener('themeChanged', function(e) {
    console.log('New theme:', e.detail.theme);
});
```

## Súbory

### SCSS
- `src/App/assets/scss/components/_dark-theme.scss` - hlavný súbor s témami
- `src/App/assets/scss/index.scss` - import dark theme

### JavaScript
- `src/App/assets/js/components/_theme-toggle.js` - logika prepínania
- `src/App/assets/js/index.js` - import theme toggle

### Templates
- `src/App/templates/layout/default.html.twig` - toggle switch v navigácii

## Použitie

### Základné použitie
Toggle switch je automaticky dostupný v navigačnej lište. Používatelia môžu:

1. **Kliknúť na toggle** pre prepnutie témy
2. **Použiť klávesovú skratku** `Ctrl/Cmd + Shift + T`
3. **Automatická detekcia** systémovej preferencie

### Pre vývojárov

#### Pridanie vlastných komponentov
```scss
.my-component {
  background-color: var(--bs-card-bg);
  color: var(--bs-body-color);
  border-color: var(--bs-border-color);
  transition: background-color 0.3s ease, color 0.3s ease;
}
```

#### JavaScript integrácia
```javascript
// Reagovanie na zmeny témy
window.addEventListener('themeChanged', function(e) {
    if (e.detail.theme === 'dark') {
        // Logika pre dark theme
        initDarkModeFeatures();
    } else {
        // Logika pre light theme
        initLightModeFeatures();
    }
});
```

## Farby

### Light Theme
- **Pozadie**: `#ffffff`
- **Text**: `#212529`
- **Navbar**: `#f8f9fa`
- **Linky**: `#0d6efd`
- **Borders**: `#dee2e6`

### Dark Theme
- **Pozadie**: `#121212`
- **Text**: `#e0e0e0`
- **Navbar**: `#1e1e1e`
- **Linky**: `#4dabf7`
- **Borders**: `#404040`

## Responzívnosť

### Desktop
- Plný toggle switch s ikonami
- Keyboard shortcuts
- Hover efekty

### Mobile
- Menší toggle switch
- Touch-friendly
- Optimalizované pre malé obrazovky

## Browser Support

- ✅ **Chrome/Edge**: Plná podpora
- ✅ **Firefox**: Plná podpora
- ✅ **Safari**: Plná podpora
- ✅ **Mobile browsers**: Plná podpora
- ⚠️ **IE11**: Základná podpora (bez CSS variables)

## Accessibility

- **ARIA labels** pre screen readery
- **Keyboard navigation** support
- **High contrast** friendly
- **Prefers-color-scheme** detection
- **Focus indicators** pre toggle

## Performance

- **CSS Variables** pre rýchle prepínanie
- **Minimal JavaScript** footprint
- **Optimalizované transitions**
- **LocalStorage** pre persistence

## Customization

### Zmena farieb
Upravte CSS variables v `_dark-theme.scss`:

```scss
[data-bs-theme="dark"] {
  --bs-body-bg: #your-color;
  --bs-body-color: #your-text-color;
  // ...
}
```

### Pridanie animácií
```scss
.my-element {
  transition: all 0.3s ease;
}
```

### Custom toggle design
Upravte `.theme-toggle` triedu v `_dark-theme.scss`.

Dark theme je plne funkčný a pripravený na produkčné použitie! 🌙✨
