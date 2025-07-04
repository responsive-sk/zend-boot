<?php

declare(strict_types=1);

namespace User\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Authentication\UserInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DashboardHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $user = $request->getAttribute(UserInterface::class);
        
        if (!$user) {
            throw new \RuntimeException('User not found in request attributes');
        }

        return new HtmlResponse($this->template->render('user::dashboard', [
            'user' => $user,
            'title' => 'Dashboard',
            'userDetails' => $user->getDetails(),
            'roles' => iterator_to_array($user->getRoles()),
        ]));
    }
}
