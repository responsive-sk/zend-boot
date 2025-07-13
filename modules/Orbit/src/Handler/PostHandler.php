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
 * Post Handler
 */
class PostHandler implements RequestHandlerInterface
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
        $slug = $request->getAttribute('slug', '');
        assert(is_string($slug));

        $content = $this->orbitManager->findContent('post', $slug);
        
        if (!$content || !$content->isPublished()) {
            return new HtmlResponse($this->template->render('error::404'), 404);
        }
        
        $content = $this->orbitManager->loadContentFromFile($content);
        
        return new HtmlResponse(
            $this->template->render('orbit::post/view-test', [
                'content' => $content,
            ])
        );
    }
}
