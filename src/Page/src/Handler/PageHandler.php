<?php

declare(strict_types=1);

namespace Light\Page\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Handler\NotFoundHandler;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twig\Error\LoaderError;

class PageHandler implements RequestHandlerInterface
{
    public function __construct(
        protected TemplateRendererInterface $template,
        protected NotFoundHandler $notFoundHandler,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actionTemplateMapping = [
            'about-us'   => 'page::about',
            'who-we-are' => 'page::who-we-are',
        ];

        $action = $request->getAttribute('action', 'index');

        $template = $actionTemplateMapping[$action] ?? null;
        if (null === $template) {
            return $this->notFoundHandler->handle($request);
        }

        try {
            return new HtmlResponse(
                $this->template->render($template)
            );
        } catch (LoaderError) {
            return $this->notFoundHandler->handle($request);
        }
    }
}
