<?php

declare(strict_types=1);

namespace Light\Page\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WhoWeAreHandler implements RequestHandlerInterface
{
    public function __construct(
        protected TemplateRendererInterface $template
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse(
            $this->template->render('page::who-we-are')
        );
    }
}
