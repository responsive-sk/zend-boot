<?php

declare(strict_types=1);

namespace AppTest\Unit\Template;

use App\Template\PhpRenderer;
use Mezzio\Template\TemplatePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for PhpRenderer
 */
#[CoversClass(PhpRenderer::class)]
class PhpRendererTest extends TestCase
{
    private PhpRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new PhpRenderer();
    }

    public function testAddPath(): void
    {
        $this->renderer->addPath('/test/path', 'test');
        $paths = $this->renderer->getPaths();
        
        $this->assertIsArray($paths);
        $this->assertNotEmpty($paths);
        $this->assertInstanceOf(TemplatePath::class, $paths[0]);
    }

    public function testGetPathsReturnsTemplatePathArray(): void
    {
        $this->renderer->addPath('/test/path1', 'namespace1');
        $this->renderer->addPath('/test/path2', 'namespace2');
        $this->renderer->addPath('/test/path3'); // default namespace
        
        $paths = $this->renderer->getPaths();
        
        $this->assertIsArray($paths);
        $this->assertCount(3, $paths);
        
        foreach ($paths as $path) {
            $this->assertInstanceOf(TemplatePath::class, $path);
        }
    }

    public function testAddDefaultParam(): void
    {
        $this->renderer->addDefaultParam('test', 'param1', 'value1');
        
        // This test just ensures the method doesn't throw an exception
        $this->assertTrue(true);
    }

    public function testRenderThrowsExceptionForMissingTemplate(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Template 'nonexistent' not found");
        
        $this->renderer->render('nonexistent');
    }
}
