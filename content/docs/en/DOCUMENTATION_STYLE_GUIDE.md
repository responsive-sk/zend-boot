# Documentation Style Guide

Štýl guide pre písanie dokumentácie v Mezzio Minimal projekte.

**Status:** Production Ready v2.0.1  
**Posledná aktualizácia:** 2025-07-12

## Prehľad

Tento dokument definuje štandardy pre písanie a formátovanie dokumentácie. Cieľom je udržať konzistentný, profesionálny a čitateľný štýl naprieč všetkými dokumentmi.

### Kľúčové Princípy

- **Jednoduchosť** - Bez zbytočných vizuálnych prvkov
- **Konzistentnosť** - Jednotný štýl naprieč dokumentmi
- **Profesionalita** - Vhodné pre enterprise prostredie
- **Čitateľnosť** - Jasná štruktúra a formátovanie

## Štruktúra Dokumentu

### Základná Štruktúra

```markdown
# Názov Dokumentu

Krátky popis účelu dokumentu (1-2 vety).

**Status:** Production Ready v2.0.1  
**Posledná aktualizácia:** YYYY-MM-DD

## Prehľad

Detailný popis obsahu dokumentu a jeho účelu.

### Kľúčové Funkcie

- **Funkcia 1** - Popis funkcie
- **Funkcia 2** - Popis funkcie
- **Funkcia 3** - Popis funkcie

## Hlavná Sekcia 1

### Podsekcia 1.1

Obsah s príkladmi kódu ak je potrebné.

## Hlavná Sekcia 2

### Podsekcia 2.1

Ďalší obsah.

---

**Ďalšie informácie:**
- [RELATED_DOC.md](RELATED_DOC.md) - Súvisiaca dokumentácia
```

## Formátovanie

### Nadpisy

```markdown
# Hlavný nadpis (H1) - len jeden na dokument
## Sekcia (H2) - hlavné sekcie
### Podsekcia (H3) - podčasti sekcií
#### Detail (H4) - len ak je nevyhnutné
```

**Pravidlá:**
- Bez emoji v nadpisoch
- Používaj výstižné názvy
- Dodržuj hierarchiu H1 → H2 → H3 → H4

### Zoznamy

```markdown
# Nečíslované zoznamy
- Prvý bod
- Druhý bod
  - Vnorený bod
  - Ďalší vnorený bod

# Číslované zoznamy
1. Prvý krok
2. Druhý krok
3. Tretí krok

# Zoznamy s popisom
- **Dôležitý bod** - Detailný popis
- **Ďalší bod** - Ďalší popis
```

### Zvýraznenie Textu

```markdown
- **Tučné** - pre dôležité pojmy
- *Kurzíva* - pre dôraz
- `kód` - pre inline kód, názvy súborov, príkazy
- **`kombinované`** - pre dôležité kódové prvky
```

### Kódové Bloky

```markdown
# Bash príkazy
```bash
composer install
php bin/migrate.php
```

# PHP kód
```php
$example = new ExampleClass();
$result = $example->process();
```

# JSON konfigurácia
```json
{
  "name": "example",
  "version": "1.0.0"
}
```

# Apache konfigurácia
```apache
RewriteEngine On
RewriteRule ^(.*)$ index.php [L]
```
```

### Tabuľky

```markdown
| Stĺpec 1 | Stĺpec 2 | Stĺpec 3 |
|----------|----------|----------|
| Hodnota 1 | Hodnota 2 | Hodnota 3 |
| Hodnota 4 | Hodnota 5 | Hodnota 6 |
```

### Odkazy

```markdown
# Interné odkazy
[RELATED_DOC.md](RELATED_DOC.md)
[/docs/en/parent/DOC.md](../parent/DOC.md)

# Externé odkazy
[Mezzio Documentation](https://docs.mezzio.dev/)

# Kotvy v dokumente
[Sekcia](#hlavná-sekcia-1)
```

## Obsah a Štýl

### Jazyk

- **Slovenčina** - pre používateľskú dokumentáciu
- **Angličtina** - pre technické referencie a API docs
- **Konzistentnosť** - nezmieš jazyk v rámci dokumentu

### Tón

- **Profesionálny** - vhodný pre enterprise
- **Jasný** - bez zbytočných slov
- **Praktický** - zameraný na riešenia
- **Priateľský** - ale nie príliš neformálny

### Príklady

```markdown
# Dobré
## Konfigurácia databázy

Nastavenie pripojenia k databáze v production prostredí.

# Zlé  
## 🗄️ Databáza konfigurácia!!! 

Super cool nastavenie DB connection 😎
```

## Špeciálne Prvky

### Status Indikátory

```markdown
**Status:** Production Ready v2.0.1
**Status:** In Development
**Status:** Deprecated
```

### Upozornenia

```markdown
**Pozor:** Dôležité upozornenie pre používateľov.

**Poznámka:** Dodatočné informácie.

**Tip:** Užitočný tip pre lepšie použitie.
```

### Príkazy a Výstupy

```markdown
# Príkaz
```bash
composer install
```

**Očakávaný výstup:**
```
Loading composer repositories with package information
Installing dependencies from lock file
```
```

## Organizácia Súborov

### Štruktúra docs/

```
docs/
├── sk/                     # Slovenská dokumentácia
│   ├── README.md          # Hlavný prehľad
│   ├── RYCHLY_START.md    # Quick start
│   └── ...
├── archive/               # Archív starších dokumentov
├── README.md             # Hlavný index
└── DOCUMENTATION_STYLE_GUIDE.md  # Tento dokument
```

### Názvy Súborov

- **VELKYMI_PISMENAMI.md** - pre hlavné dokumenty
- **snake_case.md** - pre pomocné dokumenty
- **Bez medzier** - používaj podčiarkovníky
- **Výstižné názvy** - jasne opisujú obsah

## Kontrolný Zoznam

Pred publikovaním dokumentu skontroluj:

- [ ] **Nadpis** - jasný a výstižný
- [ ] **Prehľad** - krátky popis účelu
- [ ] **Status** - aktuálny status dokumentu
- [ ] **Štruktúra** - logické členenie sekcií
- [ ] **Formátovanie** - konzistentné s týmto guide
- [ ] **Odkazy** - funkčné interné a externé odkazy
- [ ] **Kód** - správne označené kódové bloky
- [ ] **Gramatika** - bez preklepov a chýb
- [ ] **Aktuálnosť** - informácie sú aktuálne

## Príklady Dobrých Dokumentov

### Používateľská dokumentácia
- [/docs/sk/RYCHLY_START.md](sk/RYCHLY_START.md) - Návod na spustenie
- [/docs/sk/DEPLOYMENT.md](sk/DEPLOYMENT.md) - Production deployment

### Technická dokumentácia
- [API_REFERENCE.md](API_REFERENCE.md) - API referencia
- [APACHE_GUIDE.md](APACHE_GUIDE.md) - Apache konfigurácia

---

**Ďalšie informácie:**
- [/docs/sk/README.md](sk/README.md) - Slovenská dokumentácia
- [/docs/archive/TEMPLATE.md](archive/TEMPLATE.md) - Starý template (deprecated)
