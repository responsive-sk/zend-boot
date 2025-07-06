# 📄 Template pre Nové Dokumenty

Tento template používaj pri vytváraní nových dokumentov v `docs/` adresári.

## 📋 Štruktúra Dokumentu

```markdown
# 🎯 [Názov Dokumentu]

[Krátky popis účelu dokumentu - 1-2 vety]

**Status:** ✅ Production Ready v2.0.1  
**Posledná aktualizácia:** [YYYY-MM-DD]

## 📋 Prehľad

[Detailný popis obsahu dokumentu a jeho účelu]

### 🎯 Kľúčové Funkcie
- ✅ **Funkcia 1** - Popis funkcie
- ✅ **Funkcia 2** - Popis funkcie
- ✅ **Funkcia 3** - Popis funkcie

## 🏗️ [Hlavná Sekcia 1]

### [Podsekcia 1.1]

[Obsah s príkladmi kódu ak je potrebné]

```bash
# Príklad bash príkazu
composer install
```

```php
// Príklad PHP kódu
$example = new ExampleClass();
```

### [Podsekcia 1.2]

[Ďalší obsah]

## 🔧 [Hlavná Sekcia 2]

### [Podsekcia 2.1]

[Obsah]

## 📊 [Tabuľky ak sú potrebné]

| Stĺpec 1 | Stĺpec 2 | Popis |
|----------|----------|-------|
| Hodnota 1 | Hodnota 2 | Popis hodnôt |

## 🚨 [Dôležité Upozornenia]

**Poznámky:**
- **Dôležité:** Kritické informácie
- **Tip:** Užitočné rady
- **Varovanie:** Potenciálne problémy

## 📋 [Checklist ak je potrebný]

### [Kategória Checklist]
- [ ] Úloha 1
- [ ] Úloha 2
- [ ] Úloha 3

## 🔍 [Troubleshooting ak je potrebný]

### Problém: [Názov problému]

#### Riešenie:
```bash
# Kroky na riešenie
command --fix-problem
```

---

## 📚 Súvisiace Dokumenty

### 🏗️ [Kategória 1]
- **[DOCUMENT1.md](DOCUMENT1.md)** - Popis dokumentu
- **[DOCUMENT2.md](DOCUMENT2.md)** - Popis dokumentu

### 🚀 [Kategória 2]
- **[DOCUMENT3.md](DOCUMENT3.md)** - Popis dokumentu
- **[DOCUMENT4.md](DOCUMENT4.md)** - Popis dokumentu

### 🔧 [Kategória 3]
- **[DOCUMENT5.md](DOCUMENT5.md)** - Popis dokumentu
- **[DOCUMENT6.md](DOCUMENT6.md)** - Popis dokumentu

**Späť na hlavnú:** [README.md](README.md)
```

## 🎨 Štýlové Konvencie

### Emojis pre Sekcie
- 📋 Prehľad, zoznamy
- 🎯 Ciele, funkcie
- 🏗️ Architektúra, development
- 🚀 Production, deployment
- 🔒 Bezpečnosť
- ⚙️ Konfigurácia
- 🔧 Údržba, tools
- 📊 Tabuľky, štatistiky
- 🚨 Upozornenia, problémy
- ✅ Hotové úlohy
- ❌ Chyby, problémy
- 💡 Tipy, poznámky
- 📚 Odkazy, dokumentácia

### Formátovanie Textu
- **Tučné** pre dôležité pojmy
- `Kód` pre príkazy a názvy súborov
- *Kurzíva* pre zdôraznenie
- > Citáty pre dôležité poznámky

### Kódové Bloky
```bash
# Bash príkazy s komentármi
composer install --no-dev
```

```php
// PHP kód s komentármi
$service = $container->get(ServiceClass::class);
```

```apache
# Apache konfigurácia
<Directory "/var/www">
    AllowOverride All
</Directory>
```

### Odkazy
- **Interné odkazy:** `[TEXT](FILE.md)` 
- **Externé odkazy:** `[TEXT](https://example.com)`
- **Sekcie:** `[TEXT](#sekcia)`

### Tabuľky
```markdown
| Stĺpec 1 | Stĺpec 2 | Popis |
|----------|----------|-------|
| Hodnota  | Hodnota  | Popis |
```

### Zoznamy
```markdown
- ✅ Hotové úlohy
- ❌ Nehotové úlohy
- 🔄 V procese
- ⏳ Plánované
```

## 📝 Checklist pre Nový Dokument

### Pred Vytvorením
- [ ] Definovaný účel dokumentu
- [ ] Identifikovaná cieľová skupina
- [ ] Naplánovaná štruktúra obsahu
- [ ] Určené súvisiace dokumenty

### Pri Písaní
- [ ] Použitý template štruktúru
- [ ] Pridané emojis pre sekcie
- [ ] Konzistentné formátovanie
- [ ] Príklady kódu ak sú potrebné
- [ ] Tabuľky ak sú potrebné

### Po Dokončení
- [ ] Prečítané a skontrolované
- [ ] Pridané cross-references
- [ ] Aktualizovaný README.md
- [ ] Testované odkazy
- [ ] Aktualizovaný dátum

## 🔄 Aktualizácia Existujúcich Dokumentov

### Pravidelné Aktualizácie
- **Mesačne:** Skontrolovať aktuálnosť informácií
- **Pri zmenách:** Aktualizovať súvisiace dokumenty
- **Pri release:** Aktualizovať verzie a dátumy

### Verzie a Dátumy
```markdown
**Status:** ✅ Production Ready v2.0.1  
**Posledná aktualizácia:** 2025-07-06
```

---

**Použitie:** Skopíruj tento template a prispôsob ho pre nový dokument.  
**Späť na hlavnú:** [README.md](README.md)
