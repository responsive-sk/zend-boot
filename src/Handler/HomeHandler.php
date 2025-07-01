<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomeHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new HtmlResponse('
            <!DOCTYPE html>
            <html lang="sk">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="description" content="Minimálna Mezzio aplikácia s theme systémom, versioned assets a bezpečným asset managementom. Podporuje Bootstrap a TailwindCSS témy.">
                <meta name="keywords" content="Mezzio, PHP, Bootstrap, TailwindCSS, Alpine.js, theme system">
                <meta name="author" content="Mezzio Minimal App">
                <title>Mezzio Minimal Application - Theme System Demo</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; background: #f8f9fa; color: #212529; }
                    .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    h1 { color: #1a1a1a; text-align: center; font-weight: bold; }
                    .info { background: #d4edda; padding: 20px; border-radius: 5px; border-left: 4px solid #28a745; color: #155724; }
                    .badge { background: #0056b3; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.8em; font-weight: bold; }
                    a { color: #0056b3; text-decoration: none; font-weight: 500; }
                    a:hover { color: #003d82; text-decoration: underline; }
                    p { color: #495057; line-height: 1.6; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>🎉 Mezzio Minimal Application</h1>

                    <nav role="navigation" aria-label="Theme demos">
                        <p style="text-align: center; margin: 20px 0;">
                            <a href="/bootstrap-demo" aria-label="Prejsť na Bootstrap téma demo">Bootstrap Demo</a> |
                            <a href="/main-demo" aria-label="Prejsť na TailwindCSS + Alpine.js téma demo">Main Demo</a>
                        </p>
                    </nav>

                    <div class="info">
                        <p><strong>✅ Aplikácia úspešne funguje!</strong></p>
                        <p><span class="badge">Čas</span> ' . date('Y-m-d H:i:s') . '</p>
                        <p><span class="badge">PHP</span> ' . PHP_VERSION . '</p>
                        <p><span class="badge">Mezzio</span> Pripravené na rozšírenie</p>
                        <p><span class="badge">Handler</span> App\Handler\HomeHandler</p>
                    </div>
                </div>
            </body>
            </html>
        ');
    }
}
