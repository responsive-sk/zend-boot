<?php

declare(strict_types=1);

namespace Orbit\Handler;

use Orbit\Service\OrbitManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;

/**
 * Blog Handler
 * 
 * Handler pre zobrazenie blog indexu.
 */
class BlogHandler implements RequestHandlerInterface
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
        // DEBUG: Log that BlogHandler is being called
        error_log('BlogHandler::handle() called for: ' . $request->getUri()->getPath());

        $posts = $this->orbitManager->getAllContent('post', true);
        
        return new HtmlResponse(
            $this->template->render('orbit::blog/index', [
                'posts' => $posts,
                'title' => 'Blog',
                'searchEnabled' => true,
                'pagination' => [
                    'page' => 1,
                    'per_page' => 10,
                    'total' => count($posts),
                    'pages' => 1,
                ],
            ])
        );
    }
}
