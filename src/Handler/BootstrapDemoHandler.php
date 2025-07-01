<?php

declare(strict_types=1);

namespace App\Handler;

use App\Helper\AssetHelper;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BootstrapDemoHandler implements RequestHandlerInterface
{
    public function __construct(
        private AssetHelper $assetHelper
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $themeInfo = $this->assetHelper->getThemeInfo('bootstrap');
        $cssUrl = $this->assetHelper->css('bootstrap');
        $jsUrl = $this->assetHelper->js('bootstrap');

        return new HtmlResponse('
            <!DOCTYPE html>
            <html lang="sk">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="description" content="Bootstrap 5.3 téma demo pre Mezzio aplikáciu. Ukážka responzívnych komponentov, utility classes a JavaScript pluginov.">
                <meta name="keywords" content="Bootstrap, Mezzio, responsive design, CSS framework, components">
                <meta name="author" content="Mezzio Minimal App">
                <title>Bootstrap Theme Demo - Mezzio Application</title>
                <link href="' . $cssUrl . '" rel="stylesheet">
                <style>
                    /* Improved contrast for accessibility */
                    .text-muted { color: #495057 !important; }
                    .text-secondary { color: #495057 !important; }
                    .card-text { color: #212529; }
                    .list-group-item { color: #212529; }
                    p { color: #212529; }
                    .lead { color: #495057; }
                </style>
            </head>
            <body>
                <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                    <div class="container">
                        <a class="navbar-brand" href="/">Mezzio App</a>
                        <div class="navbar-nav ms-auto">
                            <a class="nav-link" href="/">Home</a>
                            <a class="nav-link active" href="/bootstrap-demo">Bootstrap Demo</a>
                            <a class="nav-link" href="/main-demo">Main Demo</a>
                        </div>
                    </div>
                </nav>

                <div class="container mt-4">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h2 class="card-title mb-0">Bootstrap Theme Demo</h2>
                                </div>
                                <div class="card-body">
                                    <p class="lead">Toto je demo stránka s Bootstrap témou.</p>
                                    <p>Bootstrap poskytuje robustný framework pre rýchly vývoj responzívnych webových aplikácií.</p>

                                    <h3>Funkcie:</h3>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">Responzívny grid systém</li>
                                        <li class="list-group-item">Predpripravené komponenty</li>
                                        <li class="list-group-item">JavaScript pluginy</li>
                                        <li class="list-group-item">Utility classes</li>
                                    </ul>

                                    <div class="mt-3">
                                        <button type="button" class="btn btn-primary me-2">Primary Button</button>
                                        <button type="button" class="btn btn-secondary me-2">Secondary Button</button>
                                        <button type="button" class="btn btn-success">Success Button</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3>Info Panel</h3>
                                </div>
                                <div class="card-body">
                                    <p><strong>Téma:</strong> ' . htmlspecialchars($themeInfo['name']) . '</p>
                                    <p><strong>Verzia:</strong> ' . htmlspecialchars($themeInfo['version']) . '</p>
                                    <p><strong>Build:</strong> Vite</p>
                                    <p><strong>CSS:</strong> ' . htmlspecialchars($cssUrl) . '</p>
                                    <p><strong>JS:</strong> ' . htmlspecialchars($jsUrl) . '</p>
                                    <p><strong>Bezpečnosť:</strong> Len povolené cesty</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="' . $jsUrl . '"></script>
            </body>
            </html>
        ');
    }
}
