<?php

declare(strict_types=1);

namespace App\Helper;

use App\Service\PathService;

class AssetHelper
{
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

        if (isset($manifest[$asset])) {
            return $this->publicPath . '/' . $theme . '/' . $manifest[$asset]['file'];
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

        if (isset($manifest[$asset]['css'])) {
            $cssFiles = $manifest[$asset]['css'];
            if (is_array($cssFiles) && !empty($cssFiles)) {
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
     */
    private function getManifest(string $theme): array
    {
        if (!isset($this->manifests[$theme])) {
            try {
                // Bezpečne validuje cestu k manifest súboru
                $manifestPath = $this->pathService->getPublicFilePath("themes/{$theme}/.vite/manifest.json");

                if (file_exists($manifestPath)) {
                    $content = file_get_contents($manifestPath);
                    $this->manifests[$theme] = json_decode($content, true) ?: [];
                } else {
                    $this->manifests[$theme] = [];
                }
            } catch (\RuntimeException $e) {
                // Ak cesta nie je bezpečná, vráti prázdny manifest
                $this->manifests[$theme] = [];
            }
        }

        return $this->manifests[$theme];
    }

    /**
     * Get all theme info including version
     */
    public function getThemeInfo(string $theme): array
    {
        try {
            // Bezpečne validuje cestu k package.json
            $packagePath = $this->pathService->getThemeFilePath("{$theme}/package.json");

            if (file_exists($packagePath)) {
                $content = file_get_contents($packagePath);
                $package = json_decode($content, true) ?: [];

                return [
                    'name' => $package['name'] ?? $theme,
                    'version' => $package['version'] ?? '1.0.0',
                    'description' => $package['description'] ?? '',
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
            if (str_contains($key, $imageName) && isset($asset['file'])) {
                return "/themes/{$theme}/" . $asset['file'];
            }
        }

        // Fallback to direct path (for development)
        return "/themes/{$theme}/assets/{$imageName}";
    }

    /**
     * Get all images from theme manifest
     */
    public function getImages(string $theme): array
    {
        $manifest = $this->getManifest($theme);
        $images = [];

        foreach ($manifest as $key => $asset) {
            if (isset($asset['file']) && preg_match('/\.(jpg|jpeg|png|gif|svg|webp)$/i', $asset['file'])) {
                $images[basename($key, '.' . pathinfo($key, PATHINFO_EXTENSION))] = "/themes/{$theme}/" . $asset['file'];
            }
        }

        return $images;
    }
}
