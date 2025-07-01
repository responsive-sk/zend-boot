<?php

declare(strict_types=1);

namespace App\Helper;

class AssetHelper
{
    private array $manifests = [];

    public function __construct(
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
            $manifestPath = __DIR__ . '/../../public/themes/' . $theme . '/.vite/manifest.json';
            
            if (file_exists($manifestPath)) {
                $content = file_get_contents($manifestPath);
                $this->manifests[$theme] = json_decode($content, true) ?: [];
            } else {
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
        $packagePath = __DIR__ . '/../../themes/' . $theme . '/package.json';
        
        if (file_exists($packagePath)) {
            $content = file_get_contents($packagePath);
            $package = json_decode($content, true) ?: [];
            
            return [
                'name' => $package['name'] ?? $theme,
                'version' => $package['version'] ?? '1.0.0',
                'description' => $package['description'] ?? '',
            ];
        }

        return [
            'name' => $theme,
            'version' => '1.0.0',
            'description' => '',
        ];
    }
}
