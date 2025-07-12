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
 * Docs Handler
 * 
 * Handler pre zobrazenie dokumentácie.
 */
class DocsHandler implements RequestHandlerInterface
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
        $lang = $request->getAttribute('lang', 'sk');
        assert(is_string($lang));

        $format = $request->getQueryParams()['format'] ?? 'html';
        assert(is_string($format));

        // Ak nie je zadaný slug, zobraz index
        if (!$slug) {
            return $this->showIndex($request, $lang, $format);
        }
        
        // Načítaj dokument
        $fullSlug = $lang . '/' . $slug;
        $content = $this->orbitManager->findContent('docs', $fullSlug);
        
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
            $this->template->render('orbit::docs/view', [
                'content' => $content,
                'navigation' => $this->buildNavigation($lang),
                'breadcrumbs' => $this->buildBreadcrumbs($content),
                'lang' => $lang,
                'searchEnabled' => true,
            ])
        );
    }

    private function showIndex(ServerRequestInterface $request, string $lang, string $format): ResponseInterface
    {
        // Získaj všetku dokumentáciu pre daný jazyk
        $allDocs = $this->orbitManager->getAllContent('docs', true);
        $langDocs = array_filter($allDocs, function($doc) use ($lang) {
            return str_starts_with($doc->getSlug(), $lang . '/');
        });
        
        if ($format === 'json') {
            return new JsonResponse([
                'docs' => array_map(fn($doc) => $doc->toArray(), $langDocs),
                'lang' => $lang,
            ]);
        }
        
        return new HtmlResponse(
            $this->template->render('orbit::docs/index', [
                'docs' => $langDocs,
                'navigation' => $this->buildNavigation($lang),
                'lang' => $lang,
                'searchEnabled' => true,
            ])
        );
    }

    private function buildNavigation(string $lang): array
    {
        $allDocs = $this->orbitManager->getAllContent('docs', true);
        $langDocs = array_filter($allDocs, function($doc) use ($lang) {
            return str_starts_with($doc->getSlug(), $lang . '/');
        });
        
        $navigation = [];
        
        foreach ($langDocs as $doc) {
            $slug = substr($doc->getSlug(), strlen($lang) + 1); // Remove lang prefix
            $title = $doc->getTitle();
            
            // Organizuj podľa priority (môže byť v meta)
            $priority = $doc->getMeta('nav_priority', 999);
            $section = $doc->getMeta('nav_section', 'Ostatné');
            
            if (!isset($navigation[$section])) {
                $navigation[$section] = [];
            }
            
            $navigation[$section][] = [
                'title' => $title,
                'slug' => $slug,
                'url' => "/docs/{$lang}/{$slug}",
                'priority' => $priority,
            ];
        }
        
        // Zoraď sekcie a položky
        foreach ($navigation as $section => &$items) {
            usort($items, fn($a, $b) => $a['priority'] <=> $b['priority']);
        }
        
        return $navigation;
    }

    private function buildBreadcrumbs(\Orbit\Entity\Content $content): array
    {
        $breadcrumbs = [
            ['title' => 'Domov', 'url' => '/'],
            ['title' => 'Dokumentácia', 'url' => '/docs'],
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
