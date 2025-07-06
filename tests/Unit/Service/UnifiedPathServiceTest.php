<?php

declare(strict_types=1);

namespace AppTest\Unit\Service;

use App\Service\UnifiedPathService;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for UnifiedPathService
 */
class UnifiedPathServiceTest extends TestCase
{
    private UnifiedPathService $pathService;

    protected function setUp(): void
    {
        $config = [
            'paths' => [
                'root' => '/test/root',
                'storage' => '/test/storage',
                'logs' => '/test/logs',
                'cache' => '/test/cache',
            ]
        ];

        $adapter = new LocalFilesystemAdapter('.');
        $filesystem = new Filesystem($adapter);

        $this->pathService = new UnifiedPathService(
            $config,
            $filesystem,
            $filesystem,
            $filesystem
        );
    }

    public function testGetRootPath(): void
    {
        $result = $this->pathService->getRootPath();
        $this->assertIsString($result);
        $this->assertEquals('/test/root', $result);
    }

    public function testStorageMethod(): void
    {
        $result = $this->pathService->storage('test.db');
        $this->assertIsString($result);
        $this->assertStringContainsString('test.db', $result);
    }

    public function testLogsMethod(): void
    {
        $result = $this->pathService->logs('app.log');
        $this->assertIsString($result);
        $this->assertStringContainsString('app.log', $result);
    }

    public function testCacheMethod(): void
    {
        $result = $this->pathService->cache('templates');
        $this->assertIsString($result);
        $this->assertStringContainsString('templates', $result);
    }

    public function testTemplatesMethod(): void
    {
        $result = $this->pathService->templates();
        $this->assertIsString($result);
    }

    public function testModuleTemplatesMethod(): void
    {
        $result = $this->pathService->moduleTemplates('User', 'user');
        $this->assertIsString($result);
        $this->assertStringContainsString('User', $result);
    }

    public function testAppTemplatesMethod(): void
    {
        $result = $this->pathService->appTemplates();
        $this->assertIsString($result);
    }
}
