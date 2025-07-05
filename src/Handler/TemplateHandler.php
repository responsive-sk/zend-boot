<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\PathService;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TemplateHandler implements RequestHandlerInterface
{
    public function __construct(private PathService $pathService)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $templateName = $request->getQueryParams()['template'] ?? 'default';

        try {
            // Bezpečné získanie cesty
            $templatePath = $this->pathService->getThemeFilePath("$templateName.html");

            // Kontrola existencie súboru
            if (!file_exists($templatePath)) {
                return new HtmlResponse('Template not found', 404);
            }

            // Alebo priame čítanie cez Flysystem
            $content = $this->pathService->readThemeFile("$templateName.html");

            return new HtmlResponse($content);
        } catch (\RuntimeException $e) {
            return new HtmlResponse('Chyba: ' . $e->getMessage(), 400);
        }
    }
}
