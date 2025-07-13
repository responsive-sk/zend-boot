<?php

declare(strict_types=1);

namespace Orbit\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Orbit\Service\OrbitManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Orbit CMS - Mark Dashboard Handler
 * 
 * Main dashboard for Orbit CMS administration
 */
class MarkDashboardHandler implements RequestHandlerInterface
{
    public function __construct(
        private OrbitManager $orbitManager,
        private TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get content statistics
        $stats = $this->getContentStats();
        
        // Get recent content (limit to 5 most recent)
        $allContent = $this->orbitManager->getAllContent(null, false);
        $recentContent = array_slice($allContent, 0, 5);
        
        // Get categories and tags
        $categories = $this->orbitManager->getAllCategories();
        $tags = $this->orbitManager->getAllTags();

        return new HtmlResponse(
            $this->template->render('orbit::mark/dashboard', [
                'title' => 'Orbit CMS - Dashboard',
                'stats' => $stats,
                'recent_content' => $recentContent,
                'categories' => $categories,
                'tags' => $tags,
            ])
        );
    }

    private function getContentStats(): array
    {
        $allContent = $this->orbitManager->getAllContent(null, false);
        
        $stats = [
            'total' => count($allContent),
            'published' => 0,
            'draft' => 0,
            'featured' => 0,
            'by_type' => [
                'post' => 0,
                'page' => 0,
                'docs' => 0,
            ],
        ];

        foreach ($allContent as $content) {
            // Count by status
            if ($content->isPublished()) {
                $stats['published']++;
            } else {
                $stats['draft']++;
            }
            
            // Count featured
            if ($content->isFeatured()) {
                $stats['featured']++;
            }
            
            // Count by type
            $type = $content->getType();
            if (isset($stats['by_type'][$type])) {
                $stats['by_type'][$type]++;
            }
        }

        return $stats;
    }
}
