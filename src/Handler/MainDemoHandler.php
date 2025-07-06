<?php

declare(strict_types=1);

namespace App\Handler;

use App\Helper\AssetHelper;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MainDemoHandler implements RequestHandlerInterface
{
    public function __construct(
        private AssetHelper $assetHelper
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $themeInfo = $this->assetHelper->getThemeInfo('main');
        $cssUrl = $this->assetHelper->css('main');
        $jsUrl = $this->assetHelper->js('main');

        return new HtmlResponse('
            <!DOCTYPE html>
            <html lang="sk">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="description" content="TailwindCSS + Alpine.js téma demo pre Mezzio aplikáciu.
                    Utility-first CSS framework s reaktívnymi komponentmi.">
                <meta name="keywords" content="TailwindCSS, Alpine.js, Mezzio, utility-first CSS, reactive components">
                <meta name="author" content="Mezzio Minimal App">
                <title>TailwindCSS + Alpine.js Demo - Mezzio Application</title>
                <link href="' . $cssUrl . '" rel="stylesheet">
            </head>
            <body class="bg-gray-50">
                <nav class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center py-4">
                            <div class="flex items-center">
                                <h1 class="text-xl font-bold text-gray-900">Mezzio App</h1>
                            </div>
                            <div class="flex space-x-6">
                                <a href="/" class="text-gray-600 hover:text-gray-900 transition-colors">Home</a>
                                <a href="/bootstrap-demo"
                                   class="text-gray-600 hover:text-gray-900 transition-colors">Bootstrap Demo</a>
                                <a href="/main-demo" class="text-primary-600 font-medium">Main Demo</a>
                            </div>
                        </div>
                    </div>
                </nav>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <div class="lg:col-span-2">
                            <div class="card">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4">TailwindCSS + Alpine.js Demo</h2>
                                <p class="text-lg text-gray-600 mb-6">Toto je demo stránka s modernou main témou.</p>
                                <p class="text-gray-600 mb-6">TailwindCSS poskytuje utility-first prístup pre rýchle
                                   a flexibilné štýlovanie, zatiaľ čo Alpine.js pridáva reaktivitu bez zložitosti.</p>

                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Funkcie:</h3>
                                <ul class="space-y-2 mb-6">
                                    <li class="flex items-center text-gray-600">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                  clip-rule="evenodd"></path>
                                        </svg>
                                        Utility-first CSS framework
                                    </li>
                                    <li class="flex items-center text-gray-600">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                  clip-rule="evenodd"></path>
                                        </svg>
                                        Reaktívne komponenty s Alpine.js
                                    </li>
                                    <li class="flex items-center text-gray-600">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                  clip-rule="evenodd"></path>
                                        </svg>
                                        Moderný dizajn systém
                                    </li>
                                    <li class="flex items-center text-gray-600">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                  d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                  clip-rule="evenodd"></path>
                                        </svg>
                                        Optimalizované pre výkon
                                    </li>
                                </ul>

                                <!-- Alpine.js Demo -->
                                <div x-data="{ open: false }" class="mb-6">
                                    <button @click="open = !open" class="btn btn-primary">
                                        <span x-text="open ? \'Skryť demo\' : \'Zobraziť Alpine.js demo\'"></span>
                                    </button>

                                    <div x-show="open" x-transition
                                         class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                        <h4 class="font-semibold text-blue-900 mb-2">Alpine.js v akcii!</h4>
                                        <p class="text-blue-700">Toto je reaktívny komponent vytvorený s Alpine.js.
                                           Kliknutím na tlačidlo sa obsah zobrazí/skryje s plynulou animáciou.</p>
                                    </div>
                                </div>

                                <div class="flex space-x-3">
                                    <button class="btn btn-primary">Primary Button</button>
                                    <button class="btn btn-secondary">Secondary Button</button>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-1">
                            <div class="card">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Info Panel</h3>
                                <div class="space-y-3 text-sm">
                                    <div>
                                        <span class="font-medium text-gray-700">Téma:</span>
                                        <span class="text-gray-600">' .
                                            htmlspecialchars($themeInfo['name']) . '</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Verzia:</span>
                                        <span class="text-gray-600">' . htmlspecialchars($themeInfo['version']) . '</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Build:</span>
                                        <span class="text-gray-600">Vite</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">CSS:</span>
                                        <span class="text-gray-600">' . htmlspecialchars($cssUrl) . '</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">JS:</span>
                                        <span class="text-gray-600">' . htmlspecialchars($jsUrl) . '</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Bezpečnosť:</span>
                                        <span class="text-gray-600">Len povolené cesty</span>
                                    </div>
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
