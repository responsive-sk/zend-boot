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
            <html>
            <head>
                <title>Mezzio Minimal</title>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; background: #f8f9fa; }
                    .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                    h1 { color: #2c3e50; text-align: center; }
                    .info { background: #e8f5e8; padding: 20px; border-radius: 5px; border-left: 4px solid #28a745; }
                    .badge { background: #007bff; color: white; padding: 4px 8px; border-radius: 3px; font-size: 0.8em; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>🎉 Mezzio Minimal Application</h1>
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
