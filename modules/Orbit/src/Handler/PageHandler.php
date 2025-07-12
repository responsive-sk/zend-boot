<?php

declare(strict_types=1);

namespace Orbit\Handler;

use Orbit\Service\OrbitManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Page Handler
 * 
 * Handler pre zobrazenie statických stránok.
 */
class PageHandler implements RequestHandlerInterface
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
        $slug = $request->getAttribute('slug');
        assert(is_string($slug));

        $format = $request->getQueryParams()['format'] ?? 'html';
        assert(is_string($format));

        // Načítaj stránku
        $content = $this->orbitManager->findContent('page', $slug);
        
        if (!$content || !$content->isPublished()) {
            return new HtmlResponse($this->template->render('error::404'), 404);
        }
        
        // Načítaj obsah zo súboru
        $content = $this->orbitManager->loadContentFromFile($content);
        
        // Vráť podľa formátu
        if ($format === 'json') {
            return new JsonResponse([
                'content' => $content->toArray(),
                'rendered_content' => $content->getRenderedContent(),
            ]);
        }
        
        // HTML response
        return new HtmlResponse(
            $this->template->render('orbit::page/view', [
                'content' => $content,
                'breadcrumbs' => $this->buildBreadcrumbs($content),
            ])
        );
    }

    private function buildBreadcrumbs(\Orbit\Entity\Content $content): array
    {
        $breadcrumbs = [
            ['title' => 'Domov', 'url' => '/'],
        ];
        
        // Pridaj kategóriu ak existuje
        if ($content->getCategory()) {
            $category = $content->getCategory();
            $breadcrumbs[] = [
                'title' => $category->getName(),
                'url' => $category->getUrl(),
            ];
        }
        
        // Pridaj aktuálnu stránku
        $breadcrumbs[] = [
            'title' => $content->getTitle(),
            'url' => $content->getUrl(),
            'active' => true,
        ];
        
        return $breadcrumbs;
    }
}
