<?php

declare(strict_types=1);

namespace App\Handler;

use App\Helper\AssetHelper;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomeHandler implements RequestHandlerInterface
{
    public function __construct(
        private AssetHelper $assetHelper,
        private ?TemplateRendererInterface $template = null
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // If no template renderer available, fall back to simple response
        if ($this->template === null) {
            return new HtmlResponse('<h1>Welcome to Root4Boot!</h1>');
        }

        // Get asset URLs from main theme
        $cssUrl = $this->assetHelper->css('main');
        $jsUrl = $this->assetHelper->js('main');

        // Prepare template data
        $data = [
            'cssUrl' => $cssUrl,
            'jsUrl' => $jsUrl,
            'images' => [
                'hdmBoot' => $this->assetHelper->image('main', 'php82'),
                'slim4' => $this->assetHelper->image('main', 'javascript'),
                'ephemeris' => $this->assetHelper->image('main', 'digital-marketing'),
            ]
        ];

        return new HtmlResponse($this->template->render('app::home', $data));
    }
}
