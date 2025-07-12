#!/usr/bin/env php
<?php
/**
 * Migration script: docs/ → content/docs/
 * 
 * Tento script presunie dokumentáciu z docs/ do content/docs/
 * pre integráciu s Orbit CMS.
 */

declare(strict_types=1);

$rootDir = dirname(__DIR__);
$docsDir = $rootDir . '/docs';
$contentDir = $rootDir . '/content';

echo "🚀 Migrácia dokumentácie do Orbit CMS\n";
echo "=====================================\n\n";

// Skontroluj, či docs/ existuje
if (!is_dir($docsDir)) {
    echo "❌ Adresár docs/ neexistuje.\n";
    exit(1);
}

// Vytvor content štruktúru
$directories = [
    $contentDir,
    $contentDir . '/docs',
    $contentDir . '/docs/sk',
    $contentDir . '/docs/en',
    $contentDir . '/docs/archive',
    $contentDir . '/pages',
    $contentDir . '/posts',
    $contentDir . '/media',
    $contentDir . '/media/images',
    $contentDir . '/media/documents',
    $contentDir . '/templates',
];

echo "📁 Vytváram adresárovú štruktúru...\n";
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "   ✅ Vytvorený: $dir\n";
    } else {
        echo "   ⏭️  Existuje: $dir\n";
    }
}

echo "\n📋 Kopírujem dokumentáciu...\n";

// Kopíruj slovenské dokumenty
if (is_dir($docsDir . '/sk')) {
    copyDirectory($docsDir . '/sk', $contentDir . '/docs/sk');
    echo "   ✅ Slovenské dokumenty: docs/sk/ → content/docs/sk/\n";
}

// Kopíruj anglické dokumenty (*.md súbory z root docs/)
$englishFiles = glob($docsDir . '/*.md');
foreach ($englishFiles as $file) {
    $filename = basename($file);
    if ($filename === 'README.md') {
        $filename = 'INDEX.md'; // Premenuj README na INDEX
    }
    copy($file, $contentDir . '/docs/en/' . $filename);
    echo "   ✅ Anglický dokument: $filename\n";
}

// Kopíruj archív
if (is_dir($docsDir . '/archive')) {
    copyDirectory($docsDir . '/archive', $contentDir . '/docs/archive');
    echo "   ✅ Archív: docs/archive/ → content/docs/archive/\n";
}

echo "\n🔗 Aktualizujem odkazy v dokumentoch...\n";

// Aktualizuj odkazy v dokumentoch
$docFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($contentDir . '/docs'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($docFiles as $file) {
    if ($file->getExtension() === 'md') {
        updateLinksInFile($file->getPathname());
        echo "   ✅ Aktualizované odkazy: " . $file->getFilename() . "\n";
    }
}

echo "\n📝 Vytváram ukážkový obsah...\n";

// Vytvor ukážkové súbory ak neexistujú
createSampleContent($contentDir);

echo "\n🎉 Migrácia dokončená!\n";
echo "=====================================\n";
echo "📁 Dokumentácia je teraz v: content/docs/\n";
echo "🌐 Bude dostupná na: /docs/sk/ a /docs/en/\n";
echo "⚙️  Ďalší krok: Implementácia Orbit CMS modulu\n\n";

/**
 * Kopíruje adresár rekurzívne
 */
function copyDirectory(string $source, string $destination): void
{
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            copy($item->getPathname(), $target);
        }
    }
}

/**
 * Aktualizuje odkazy v Markdown súbore
 */
function updateLinksInFile(string $filePath): void
{
    $content = file_get_contents($filePath);
    
    // Aktualizuj relatívne odkazy
    $content = preg_replace('/\[([^\]]+)\]\(sk\/([^)]+)\)/', '[$1](/docs/sk/$2)', $content);
    $content = preg_replace('/\[([^\]]+)\]\(\.\.\/([^)]+)\)/', '[$1](/docs/en/$2)', $content);
    $content = preg_replace('/\[([^\]]+)\]\(archive\/([^)]+)\)/', '[$1](/docs/archive/$2)', $content);
    
    file_put_contents($filePath, $content);
}

/**
 * Vytvorí ukážkový obsah
 */
function createSampleContent(string $contentDir): void
{
    // Ukážková stránka
    if (!file_exists($contentDir . '/pages/about.md')) {
        $aboutContent = <<<'MD'
---
title: "O projekte"
slug: "about"
description: "Informácie o Mezzio Minimal projekte"
published: true
created_at: "2025-01-12"
---

# O projekte

Mezzio Minimal je moderná PHP aplikácia s Orbit CMS integráciou.
MD;
        file_put_contents($contentDir . '/pages/about.md', $aboutContent);
        echo "   ✅ Vytvorená ukážková stránka: about.md\n";
    }
    
    // Template súbory
    if (!file_exists($contentDir . '/templates/page.md')) {
        $pageTemplate = <<<'MD'
---
title: "Názov stránky"
slug: "url-slug"
description: "Popis stránky"
published: true
created_at: "2025-01-12"
---

# Názov stránky

Obsah stránky v Markdown formáte.
MD;
        file_put_contents($contentDir . '/templates/page.md', $pageTemplate);
        echo "   ✅ Vytvorený template: page.md\n";
    }
}
