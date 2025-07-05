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
        private SystemStatsService $statsService
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

            // Prepare dashboard data
            $dashboardData = [
                'mark_user' => $markUser,
                'user_roles' => $userRoles,
                'is_supermark' => $isSupermark,
                'is_editor' => $isEditor,
                'stats' => $stats,
                'recent_users' => $recentUsers,
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
}
