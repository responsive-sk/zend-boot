<?php

declare(strict_types=1);

namespace Orbit\Handler;

use Orbit\Service\OrbitManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Mark Content Handler
 * 
 * Handler pre správu obsahu cez Mark rozhranie.
 */
class MarkContentHandler implements RequestHandlerInterface
{
    private OrbitManager $orbitManager;
    private TemplateRendererInterface $template;

    public function __construct(
        OrbitManager $orbitManager,
        TemplateRendererInterface $template
    ) {
        $this->orbitManager = $orbitManager;
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $action = $this->getAction($request);
        $type = $request->getAttribute('type');
        assert(is_string($type) || $type === null);

        $id = $request->getAttribute('id');
        assert(is_string($id) || is_int($id) || $id === null);

        $idInt = $id !== null ? (int) $id : 0;

        return match ($action) {
            'index' => $this->listContent($request, $type),
            'create' => $this->createContent($request, $type),
            'edit' => $this->editContent($request, $type, $idInt),
            'delete' => $this->deleteContent($request, $type, $idInt),
            default => $this->listContent($request, $type),
        };
    }

    private function getAction(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        
        if (str_contains($path, '/create')) {
            return 'create';
        }
        
        if (str_contains($path, '/edit')) {
            return 'edit';
        }
        
        if (str_contains($path, '/delete')) {
            return 'delete';
        }
        
        return 'index';
    }

    private function listContent(ServerRequestInterface $request, ?string $type): ResponseInterface
    {
        $allContent = $this->orbitManager->getAllContent($type, false);
        
        // Group by type if no specific type
        $contentByType = [];
        if (!$type) {
            foreach ($allContent as $content) {
                $contentByType[$content->getType()][] = $content;
            }
        } else {
            $contentByType[$type] = $allContent;
        }

        return new HtmlResponse(
            $this->template->render('orbit::mark/content/index', [
                'content_by_type' => $contentByType,
                'current_type' => $type,
                'total_content' => count($allContent),
                'title' => 'Správa Obsahu - Orbit CMS',
            ])
        );
    }

    private function createContent(ServerRequestInterface $request, ?string $type): ResponseInterface
    {
        if ($request->getMethod() === 'POST') {
            return $this->processCreateContent($request, $type);
        }

        $categories = $this->orbitManager->getAllCategories();
        $tags = $this->orbitManager->getAllTags();

        return new HtmlResponse(
            $this->template->render('orbit::mark/content/create', [
                'content_type' => $type ?? 'page',
                'categories' => $categories,
                'tags' => $tags,
                'title' => 'Nový Obsah - Orbit CMS',
            ])
        );
    }

    private function editContent(ServerRequestInterface $request, ?string $type, int $id): ResponseInterface
    {
        $content = $this->orbitManager->findContentById($id);
        
        if (!$content) {
            return new HtmlResponse('Content not found', 404);
        }

        if ($request->getMethod() === 'POST') {
            return $this->processEditContent($request, $content);
        }

        // Load content from file
        $content = $this->orbitManager->loadContentFromFile($content);
        $categories = $this->orbitManager->getAllCategories();
        $tags = $this->orbitManager->getAllTags();

        return new HtmlResponse(
            $this->template->render('orbit::mark/content/edit', [
                'content' => $content,
                'categories' => $categories,
                'tags' => $tags,
                'title' => 'Editácia: ' . $content->getTitle(),
            ])
        );
    }

    private function deleteContent(ServerRequestInterface $request, ?string $type, int $id): ResponseInterface
    {
        if ($request->getMethod() !== 'POST') {
            return new RedirectResponse('/mark/orbit/content');
        }

        $content = $this->orbitManager->findContentById($id);
        
        if ($content) {
            $this->orbitManager->deleteContent($content);
        }

        return new RedirectResponse('/mark/orbit/content?deleted=1');
    }

    private function processCreateContent(ServerRequestInterface $request, ?string $type): ResponseInterface
    {
        $data = $request->getParsedBody();
        assert(is_array($data));

        try {
            $contentData = [
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'published' => isset($data['published']),
                'featured' => isset($data['featured']),
                'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
                'meta_data' => [
                    'description' => $data['description'] ?? '',
                    'keywords' => $data['keywords'] ?? '',
                    'author' => $data['author'] ?? '',
                ],
                'body' => $data['body'] ?? '',
            ];

            $content = $this->orbitManager->createContent($type ?? 'page', $contentData);
            
            return new RedirectResponse("/mark/orbit/content/{$content->getType()}/{$content->getId()}/edit?created=1");
            
        } catch (\Exception $e) {
            $categories = $this->orbitManager->getAllCategories();
            $tags = $this->orbitManager->getAllTags();

            // Better error message for unique constraint violations
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'UNIQUE constraint failed: orbit_content.slug')) {
                $errorMessage = "URL slug '{$data['slug']}' už existuje. Prosím, použite iný slug.";
            }

            return new HtmlResponse(
                $this->template->render('orbit::mark/content/create', [
                    'content_type' => $type ?? 'page',
                    'categories' => $categories,
                    'tags' => $tags,
                    'form_data' => $data,
                    'error' => $errorMessage,
                    'title' => 'Nový Obsah - Orbit CMS',
                ])
            );
        }
    }

    private function processEditContent(ServerRequestInterface $request, \Orbit\Entity\Content $content): ResponseInterface
    {
        $data = $request->getParsedBody();
        assert(is_array($data));

        try {
            $updateData = [
                'title' => $data['title'] ?? $content->getTitle(),
                'slug' => $data['slug'] ?? $content->getSlug(),
                'published' => isset($data['published']),
                'featured' => isset($data['featured']),
                'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
                'meta_data' => [
                    'description' => $data['description'] ?? '',
                    'keywords' => $data['keywords'] ?? '',
                    'author' => $data['author'] ?? '',
                ],
                'body' => $data['body'] ?? '',
            ];

            $this->orbitManager->updateContent($content, $updateData);
            
            return new RedirectResponse("/mark/orbit/content/{$content->getType()}/{$content->getId()}/edit?updated=1");

        } catch (\Exception $e) {
            $categories = $this->orbitManager->getAllCategories();
            $tags = $this->orbitManager->getAllTags();

            // Better error message for unique constraint violations
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, 'UNIQUE constraint failed: orbit_content.slug')) {
                $errorMessage = "URL slug '{$data['slug']}' už existuje. Prosím, použite iný slug.";
            }

            return new HtmlResponse(
                $this->template->render('orbit::mark/content/edit', [
                    'content' => $content,
                    'categories' => $categories,
                    'tags' => $tags,
                    'form_data' => $data,
                    'error' => $errorMessage,
                    'title' => 'Editácia: ' . $content->getTitle(),
                ])
            );
        }
    }
}
