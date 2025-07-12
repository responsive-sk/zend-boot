<?php

declare(strict_types=1);

namespace Orbit\Service\FileDriver;

/**
 * File Driver Interface
 * 
 * Interface pre prácu s rôznymi typmi súborov (Markdown, JSON, YAML).
 */
interface FileDriverInterface
{
    /**
     * Načíta súbor a vráti parsované dáta
     */
    public function read(string $path): array;

    /**
     * Zapíše dáta do súboru
     */
    public function write(string $path, array $data): bool;

    /**
     * Skontroluje, či súbor existuje
     */
    public function exists(string $path): bool;

    /**
     * Zmaže súbor
     */
    public function delete(string $path): bool;

    /**
     * Renderuje obsah (napr. Markdown → HTML)
     */
    public function render(string $content): string;

    /**
     * Vráti podporované prípony súborov
     */
    public function getSupportedExtensions(): array;
}
