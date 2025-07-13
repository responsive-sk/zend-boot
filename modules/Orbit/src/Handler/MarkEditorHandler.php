<?php

declare(strict_types=1);

namespace Orbit\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Template\TemplateRendererInterface;
use Orbit\Service\OrbitManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Orbit CMS - Mark Editor Handler
 * 
 * Advanced editor for Orbit CMS content with live preview
 */
class MarkEditorHandler implements RequestHandlerInterface
{
    public function __construct(
        private OrbitManager $orbitManager,
        private TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $action = $this->getAction($request);
        
        return match ($action) {
            'preview' => $this->handlePreview($request),
            default => $this->showEditor($request),
        };
    }

    private function getAction(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        
        if (str_contains($path, '/preview')) {
            return 'preview';
        }
        
        return 'editor';
    }

    private function showEditor(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');
        $content = null;

        if ($id !== null) {
            assert(is_string($id) || is_int($id));
            $content = $this->orbitManager->findContentById((int) $id);
            if ($content) {
                $content = $this->orbitManager->loadContentFromFile($content);
            }
        }
        
        $categories = $this->orbitManager->getAllCategories();
        $tags = $this->orbitManager->getAllTags();

        return new HtmlResponse(
            $this->template->render('orbit::mark/editor', [
                'content' => $content,
                'categories' => $categories,
                'tags' => $tags,
                'title' => $content ? 'Editor: ' . $content->getTitle() : 'Nový obsah - Editor',
            ])
        );
    }

    private function handlePreview(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        assert(is_array($data));
        
        $markdown = $data['markdown'] ?? '';
        
        // Simple markdown to HTML conversion for preview
        $html = $this->convertMarkdownToHtml($markdown);
        
        return new JsonResponse([
            'html' => $html,
            'success' => true,
        ]);
    }

    private function convertMarkdownToHtml(string $markdown): string
    {
        // Basic markdown conversion - in production you'd use a proper parser
        $html = $markdown;

        // Headers
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html) ?? $html;
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html) ?? $html;
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html) ?? $html;

        // Bold and italic
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html) ?? $html;
        $html = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $html) ?? $html;

        // Links
        $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $html) ?? $html;

        // Code blocks
        $html = preg_replace('/```(.+?)```/s', '<pre><code>$1</code></pre>', $html) ?? $html;
        $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html) ?? $html;

        // Line breaks
        $html = nl2br($html);

        return $html;
    }
}
