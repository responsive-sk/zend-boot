# Bezpečnosť - Path Traversal Protection

## Prehľad

Táto aplikácia implementuje kompletnú ochranu proti path traversal útokom pomocou `PathService` a Flysystem knižnice.

## Implementované ochranné opatrenia

### 1. PathService

Centralizovaná služba pre bezpečnú prácu s cestami:

```php
// Bezpečné načítanie súboru
$pathService = $container->get(PathService::class);
$safePath = $pathService->getPublicFilePath('user/avatar.jpg');
```

### 2. Validácia ciest

- **Odstránenie nebezpečných znakov**: `..`, `\`, `<>:"|?*`
- **Realpath validácia**: Kontrola, či finálna cesta je v rámci povoleného adresára
- **Normalizácia ciest**: Automatické čistenie a formátovanie

### 3. Flysystem integrácia

Použitie League Flysystem pre bezpečné operácie so súbormi:

```php
// Bezpečné čítanie súboru
$content = $pathService->readPublicFile('safe/path.txt');

// Kontrola existencie súboru
if ($pathService->publicFileExists('image.jpg')) {
    // Súbor existuje
}
```

## Použitie v kóde

### AssetHelper

```php
// Automaticky validované cesty
$assetHelper = $container->get(AssetHelper::class);
$cssUrl = $assetHelper->css('bootstrap');  // Bezpečné
$jsUrl = $assetHelper->js('main');         // Bezpečné
$imageUrl = $assetHelper->image('main', 'logo'); // Bezpečné
```

### Príklad v Handler

```php
class MyHandler implements RequestHandlerInterface
{
    public function __construct(
        private AssetHelper $assetHelper,
        private PathService $pathService
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Bezpečné získanie asset URL
        $cssUrl = $this->assetHelper->css('bootstrap');

        // Bezpečná validácia cesty
        try {
            $safePath = $this->pathService->getPublicFilePath('uploads/file.txt');
            $content = file_get_contents($safePath);
        } catch (\RuntimeException $e) {
            // Path traversal pokus zablokovaný
            return new HtmlResponse('Invalid path', 400);
        }

        return new HtmlResponse($content);
    }
}
```

### Priame použitie PathService

```php
try {
    // Validácia cesty k public súboru
    $publicPath = $pathService->getPublicFilePath('themes/main/style.css');
    
    // Validácia cesty k téme
    $themePath = $pathService->getThemeFilePath('bootstrap/package.json');
    
    // Generovanie bezpečnej URL
    $url = $pathService->getPublicUrl('images/logo.png');
    
} catch (\RuntimeException $e) {
    // Neplatná cesta - potenciálny útok
    error_log('Path traversal attempt: ' . $e->getMessage());
}
```

## Testovanie bezpečnosti

Spustite testy pre overenie ochrany:

```bash
composer test tests/Service/PathServiceTest.php
```

## Zakázané vzory

Tieto vzory sú automaticky blokované:

- `../../../etc/passwd`
- `folder/../../../sensitive.txt`
- `folder\\..\\..\sensitive.txt`
- `file<script>.txt`
- Akékoľvek cesty s `..` segmentmi

## Konfigurácia

Cesty sú definované v `config/autoload/paths.global.php`:

```php
return [
    'paths' => [
        'root' => $rootDir,
        'public' => "$rootDir/public",
        'themes' => "$rootDir/themes",
        'uploads' => "$rootDir/data/uploads",
    ],
];
```

## Monitoring

Pre produkčné prostredie odporúčame:

1. **Logovanie pokusov o path traversal**
2. **Rate limiting** pre podozrivé požiadavky
3. **WAF pravidlá** pre dodatočnú ochranu
4. **Pravidelné bezpečnostné audity**

## Aktualizácie

Pri pridávaní nových funkcií vždy:

1. Použite `PathService` pre validáciu ciest
2. Nikdy nepracujte priamo s `$_GET`, `$_POST` cestami
3. Testujte proti path traversal útokom
4. Dokumentujte bezpečnostné opatrenia
