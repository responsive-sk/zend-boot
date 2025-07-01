<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BootstrapDemoHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse('
            <!DOCTYPE html>
            <html lang="sk">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title>Bootstrap Theme Demo</title>
                <link href="/themes/bootstrap/assets/main.css" rel="stylesheet">
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
                                    
                                    <h4>Funkcie:</h4>
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
                                    <h5>Info Panel</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Téma:</strong> Bootstrap 5.3</p>
                                    <p><strong>Build:</strong> Vite</p>
                                    <p><strong>Assets:</strong> /themes/bootstrap/</p>
                                    <p><strong>Bezpečnosť:</strong> Len povolené cesty</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script src="/themes/bootstrap/assets/main.js"></script>
            </body>
            </html>
        ');
    }
}
