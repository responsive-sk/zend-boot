<?php

declare(strict_types=1);

namespace User\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Service\UserRepository;

class AdminHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private UserRepository $userRepository
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);

        if (!$user) {
            throw new \RuntimeException('User not found in request attributes');
        }

        // Get all users for admin panel
        $allUsers = $this->userRepository->findAll();
        $adminUsers = $this->userRepository->findByRole('admin');
        $regularUsers = $this->userRepository->findByRole('user');

        return new HtmlResponse($this->template->render('user::admin', [
            'user' => $user,
            'title' => 'Admin Panel',
            'allUsers' => $allUsers,
            'adminUsers' => $adminUsers,
            'regularUsers' => $regularUsers,
            'stats' => [
                'total_users' => count($allUsers),
                'admin_users' => count($adminUsers),
                'regular_users' => count($regularUsers),
            ],
        ]));
    }
}
