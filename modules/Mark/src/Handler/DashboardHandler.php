<?php

declare(strict_types=1);

namespace Mark\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mark\Entity\MarkUser;
use Mark\Service\MarkUserRepository;
use Mark\Service\SystemStatsService;
use Orbit\Service\OrbitManager;

/**
 * HDM Boot Protocol - Mark Dashboard Handler
 *
 * System dashboard accessible only to mark users
 * Provides system overview and management tools
 */
class DashboardHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private MarkUserRepository $markUserRepository,
        private SystemStatsService $statsService,
        private OrbitManager $orbitManager
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $markUser = $request->getAttribute('mark_user');

        if (!$markUser instanceof MarkUser) {
            return new HtmlResponse('Mark user not found in request', 500);
        }

        try {
            $userRoles = $markUser->getRoles();
            $isSupermark = in_array('supermark', $userRoles, true);
            $isEditor = in_array('editor', $userRoles, true);

            // Get system statistics
            $stats = $this->statsService->getSystemStats();

            // Get recent activity
            $recentUsers = $this->markUserRepository->findRecentlyActive(10);

            // Get Orbit CMS statistics
            $orbitStats = $this->getOrbitStats();

            // Prepare dashboard data
            $dashboardData = [
                'mark_user' => $markUser,
                'user_roles' => $userRoles,
                'is_supermark' => $isSupermark,
                'is_editor' => $isEditor,
                'stats' => $stats,
                'recent_users' => $recentUsers,
                'orbit_stats' => $orbitStats,
                'title' => 'Mark Dashboard - HDM Boot Protocol',
            ];

            return new HtmlResponse($this->template->render('mark::dashboard', $dashboardData));
        } catch (\Exception $e) {
            return new HtmlResponse(
                'Dashboard Error: ' . $e->getMessage() . '<br>File: ' . $e->getFile() . ':' . $e->getLine(),
                500
            );
        }
    }

    private function getOrbitStats(): array
    {
        try {
            // Get real stats from OrbitManager
            $allContent = $this->orbitManager->getAllContent(null, false);
            $publishedContent = $this->orbitManager->getAllContent(null, true);

            $stats = [
                'total_content' => count($allContent),
                'published_content' => count($publishedContent),
                'docs_count' => 0,
                'pages_count' => 0,
                'posts_count' => 0,
                'categories_count' => 0,
                'tags_count' => 0,
                'recent_content' => [],
            ];

            // Count by type
            foreach ($allContent as $content) {
                switch ($content->getType()) {
                    case 'docs':
                        $stats['docs_count']++;
                        break;
                    case 'page':
                        $stats['pages_count']++;
                        break;
                    case 'post':
                        $stats['posts_count']++;
                        break;
                }
            }

            // Get categories and tags count
            $categories = $this->orbitManager->getAllCategories();
            $tags = $this->orbitManager->getAllTags();

            $stats['categories_count'] = count($categories);
            $stats['tags_count'] = count($tags);

            // Get recent content (last 3)
            $recentContent = array_slice($publishedContent, 0, 3);
            $stats['recent_content'] = array_map(function($content) {
                return [
                    'title' => $content->getTitle(),
                    'type' => $content->getType(),
                    'url' => $content->getUrl(),
                    'updated_at' => $content->getUpdatedAt(),
                ];
            }, $recentContent);

            return $stats;

        } catch (\Exception $e) {
            // Return empty stats on error
            return [
                'total_content' => 0,
                'published_content' => 0,
                'docs_count' => 0,
                'pages_count' => 0,
                'posts_count' => 0,
                'categories_count' => 0,
                'tags_count' => 0,
                'recent_content' => [],
                'error' => $e->getMessage(),
            ];
        }
    }
}
