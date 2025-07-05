<?php

declare(strict_types=1);

namespace App\Helper;

use App\Service\PathService;

class AssetHelper
{
    /** @var array<string, mixed> */
    private array $manifests = [];

    public function __construct(
        private PathService $pathService,
        private string $publicPath = '/themes'
    ) {
    }

    /**
     * Get asset URL with hash for cache busting
     */
    public function asset(string $theme, string $asset): string
    {
        $manifest = $this->getManifest($theme);

        if (isset($manifest[$asset]) && is_array($manifest[$asset]) && isset($manifest[$asset]['file'])) {
            $file = $manifest[$asset]['file'];
            if (is_string($file)) {
                return $this->publicPath . '/' . $theme . '/' . $file;
            }
        }

        // Fallback for development mode without hash
        return $this->publicPath . '/' . $theme . '/assets/' . $asset;
    }

    /**
     * Get CSS asset URL
     */
    public function css(string $theme, string $asset = 'main.js'): string
    {
        $manifest = $this->getManifest($theme);

        if (isset($manifest[$asset]) && is_array($manifest[$asset]) && isset($manifest[$asset]['css'])) {
            $cssFiles = $manifest[$asset]['css'];
            if (is_array($cssFiles) && !empty($cssFiles) && is_string($cssFiles[0])) {
                return $this->publicPath . '/' . $theme . '/' . $cssFiles[0];
            }
        }

        // Fallback
        return $this->publicPath . '/' . $theme . '/assets/main.css';
    }

    /**
     * Get JS asset URL
     */
    public function js(string $theme, string $asset = 'main.js'): string
    {
        return $this->asset($theme, $asset);
    }

    /**
     * Load and cache manifest file
     * @return array<string, mixed>
     */
    private function getManifest(string $theme): array
    {
        if (!isset($this->manifests[$theme])) {
            try {
                // Bezpečne validuje cestu k manifest súboru
                $manifestPath = $this->pathService->getPublicFilePath("themes/{$theme}/.vite/manifest.json");

                if (file_exists($manifestPath)) {
                    $content = file_get_contents($manifestPath);
                    if ($content !== false) {
                        $decoded = json_decode($content, true);
                        $this->manifests[$theme] = is_array($decoded) ? $decoded : [];
                    } else {
                        $this->manifests[$theme] = [];
                    }
                } else {
                    $this->manifests[$theme] = [];
                }
            } catch (\RuntimeException $e) {
                // Ak cesta nie je bezpečná, vráti prázdny manifest
                $this->manifests[$theme] = [];
            }
        }

        $manifest = $this->manifests[$theme];
        assert(is_array($manifest));
        return $manifest;
    }

    /**
     * Get all theme info including version
     * @return array<string, string>
     */
    public function getThemeInfo(string $theme): array
    {
        try {
            // Bezpečne validuje cestu k package.json
            $packagePath = $this->pathService->getThemeFilePath("{$theme}/package.json");

            if (file_exists($packagePath)) {
                $content = file_get_contents($packagePath);
                if ($content === false) {
                    throw new \RuntimeException("Unable to read package.json");
                }

                $package = json_decode($content, true);
                if (!is_array($package)) {
                    $package = [];
                }

                return [
                    'name' => is_string($package['name'] ?? null) ? $package['name'] : $theme,
                    'version' => is_string($package['version'] ?? null) ? $package['version'] : '1.0.0',
                    'description' => is_string($package['description'] ?? null) ? $package['description'] : '',
                ];
            }
        } catch (\RuntimeException $e) {
            // Ak cesta nie je bezpečná, vráti default hodnoty
        }

        return [
            'name' => $theme,
            'version' => '1.0.0',
            'description' => '',
        ];
    }

    /**
     * Get image URL from theme assets
     */
    public function image(string $theme, string $imageName): string
    {
        $manifest = $this->getManifest($theme);

        // Look for image in manifest
        foreach ($manifest as $key => $asset) {
            if (str_contains($key, $imageName) && is_array($asset) && isset($asset['file']) && is_string($asset['file'])) {
                return "/themes/{$theme}/" . $asset['file'];
            }
        }

        // Fallback to direct path (for development)
        return "/themes/{$theme}/assets/{$imageName}";
    }

    /**
     * Get all images from theme manifest
     * @return array<string>
     */
    public function getImages(string $theme): array
    {
        $manifest = $this->getManifest($theme);
        $images = [];

        foreach ($manifest as $key => $asset) {
            if (is_array($asset) && isset($asset['file']) && is_string($asset['file'])) {
                $file = $asset['file'];
                if (preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $file)) {
                    $images[basename($key, '.' . pathinfo($key, PATHINFO_EXTENSION))] = "/themes/{$theme}/" . $file;
                }
            }
        }

        return $images;
    }
}
