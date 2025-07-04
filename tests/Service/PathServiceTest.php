<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\PathService;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

class PathServiceTest extends TestCase
{
    private PathService $pathService;
    private string $tempDir;

    protected function setUp(): void
    {
        // Vytvorenie dočasného adresára pre testy
        $this->tempDir = sys_get_temp_dir() . '/pathservice_test_' . uniqid();
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/public');
        mkdir($this->tempDir . '/themes');
        mkdir($this->tempDir . '/uploads');

        $config = [
            'paths' => [
                'root' => $this->tempDir,
                'public' => $this->tempDir . '/public',
                'themes' => $this->tempDir . '/themes',
                'uploads' => $this->tempDir . '/uploads',
            ]
        ];

        // Použitie mock objektov pre Filesystem
        $this->pathService = new PathService(
            $config,
            $this->createMock(Filesystem::class),
            $this->createMock(Filesystem::class),
            $this->createMock(Filesystem::class)
        );
    }

    protected function tearDown(): void
    {
        // Vyčistenie dočasného adresára
        $this->removeDirectory($this->tempDir);
    }

    public function testValidPath(): void
    {
        $validPath = $this->pathService->getPublicFilePath('test.txt');
        $this->assertStringStartsWith($this->tempDir . '/public', $validPath);
    }

    public function testPathTraversalAttack(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Neplatná cesta');
        
        $this->pathService->getPublicFilePath('../../../etc/passwd');
    }

    public function testPathWithDotDot(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Neplatná cesta');
        
        $this->pathService->getPublicFilePath('folder/../../../sensitive.txt');
    }

    public function testPathWithBackslash(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Neplatná cesta');
        
        $this->pathService->getPublicFilePath('folder\\..\\..\\sensitive.txt');
    }

    public function testPathWithSpecialCharacters(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Neplatná cesta');
        
        $this->pathService->getPublicFilePath('file<script>.txt');
    }

    public function testThemeFilePath(): void
    {
        $validPath = $this->pathService->getThemeFilePath('bootstrap/package.json');
        $this->assertStringStartsWith($this->tempDir . '/themes', $validPath);
    }

    public function testUploadFilePath(): void
    {
        $validPath = $this->pathService->getUploadFilePath('user123/avatar.jpg');
        $this->assertStringStartsWith($this->tempDir . '/uploads', $validPath);
    }

    public function testGetPublicUrl(): void
    {
        $url = $this->pathService->getPublicUrl('css/style.css');
        $this->assertEquals('/css/style.css', $url);
    }

    public function testGetThemeUrl(): void
    {
        $url = $this->pathService->getThemeUrl('bootstrap/assets/main.css');
        $this->assertEquals('/themes/bootstrap/assets/main.css', $url);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
