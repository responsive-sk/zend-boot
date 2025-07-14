<?php

declare(strict_types=1);

namespace Light\App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ResponsiveSk\Slim4Paths\Paths;

class GetIndexViewHandler implements RequestHandlerInterface
{
    public function __construct(
        protected TemplateRendererInterface $template,
        protected Paths $paths
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Example of using Paths service in template data
        $templateData = [
            'paths' => [
                'public' => $this->paths->public(),
                'assets' => $this->paths->assets(),
                'css'    => $this->paths->css(),
                'js'     => $this->paths->js(),
                'images' => $this->paths->images(),
            ],
        ];

        return new HtmlResponse(
            $this->template->render('app::index', $templateData)
        );
    }
}
