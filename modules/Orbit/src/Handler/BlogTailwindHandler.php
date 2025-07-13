<?php

declare(strict_types=1);

namespace Orbit\Handler;

use App\Helper\AssetHelper;
use Orbit\Service\OrbitManager;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class BlogTailwindHandler implements RequestHandlerInterface
{
    public function __construct(
        private OrbitManager $orbitManager,
        private AssetHelper $assetHelper
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get all published posts
        $posts = $this->orbitManager->getAllContent('post', true);
        
        // Get Tailwind theme assets
        $themeInfo = $this->assetHelper->getThemeInfo('main');
        $cssUrl = $this->assetHelper->css('main');
        $jsUrl = $this->assetHelper->js('main');

        // Fallback to direct paths if AssetHelper fails
        if (strpos($cssUrl, 'main.css') !== false) {
            $cssUrl = '/themes/main/assets/main-D-jYNHe5.css';
        }
        if (strpos($jsUrl, 'main.js') !== false) {
            $jsUrl = '/themes/main/assets/main-A67U7hHX.js';
        }

        return new HtmlResponse('
            <!DOCTYPE html>
            <html lang="sk" x-data="{ darkMode: false }" :class="{ \'dark\': darkMode }">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta name="description" content="Blog príspevky - najnovšie články a novinky z Orbit CMS s Tailwind CSS">
                <meta name="keywords" content="Orbit CMS, blog, Tailwind CSS, Alpine.js, články">
                <meta name="author" content="Orbit CMS">
                <title>Blog - Orbit CMS</title>
                <link rel="icon" href="/favicon.ico" type="image/x-icon">
                <link href="' . $cssUrl . '" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            </head>
            <body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
                <!-- Navigation -->
                <nav class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex justify-between items-center py-4">
                            <!-- Logo -->
                            <div class="flex items-center">
                                <a href="/" class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-satellite-dish text-white text-sm"></i>
                                    </div>
                                    <span class="text-xl font-bold text-gray-900 dark:text-white">Orbit CMS</span>
                                </a>
                            </div>
                            
                            <!-- Desktop Navigation -->
                            <div class="hidden md:flex items-center space-x-6">
                                <a href="/" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    <i class="fas fa-home me-1"></i>Domov
                                </a>
                                <a href="/blog-tailwind" class="text-blue-600 dark:text-blue-400 font-medium">
                                    <i class="fas fa-blog me-1"></i>Blog
                                </a>
                                <a href="/docs" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    <i class="fas fa-book me-1"></i>Docs
                                </a>
                                <a href="/page/about" class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    <i class="fas fa-info-circle me-1"></i>O projekte
                                </a>
                                
                                <!-- Dark Mode Toggle -->
                                <button @click="darkMode = !darkMode" 
                                        class="text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                                    <i x-show="!darkMode" class="fas fa-moon"></i>
                                    <i x-show="darkMode" class="fas fa-sun"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Hero Section -->
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 py-16">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mb-6">
                            <i class="fas fa-blog text-white text-2xl"></i>
                        </div>
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                            Blog s Tailwind CSS
                        </h1>
                        <p class="text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Najnovšie články a novinky z Orbit CMS s moderným portfolio-grid dizajnom
                        </p>
                    </div>
                </div>

                <!-- Blog Posts -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                    ' . $this->renderBlogPosts($posts) . '
                </div>

                <!-- Footer -->
                <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-16">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                        <div class="text-center">
                            <p class="text-gray-600 dark:text-gray-300 text-sm">
                                © 2025 Orbit CMS. Vytvorené s ❤️ pomocou Tailwind CSS a Alpine.js
                            </p>
                        </div>
                    </div>
                </footer>

                <script src="' . $jsUrl . '"></script>
                
                <!-- Dark mode persistence -->
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        // Load dark mode preference
                        if (localStorage.getItem("darkMode") === "true") {
                            document.documentElement.classList.add("dark");
                        }
                        
                        // Watch for dark mode changes
                        document.addEventListener("alpine:init", () => {
                            Alpine.effect(() => {
                                if (Alpine.store && Alpine.store("darkMode")) {
                                    localStorage.setItem("darkMode", Alpine.store("darkMode"));
                                }
                            });
                        });
                    });
                </script>
            </body>
            </html>
        ');
    }

    private function renderBlogPosts(array $posts): string
    {
        if (empty($posts)) {
            return '
                <div class="text-center py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-6">
                        <i class="fas fa-file-alt text-gray-400 dark:text-gray-500 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Žiadne príspevky</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        Momentálne nie sú publikované žiadne blog príspevky.
                    </p>
                    <a href="/mark/orbit/content" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-plus me-2"></i>
                        Vytvoriť prvý príspevok
                    </a>
                </div>
            ';
        }

        $html = '<div class="portfolio-grid">';
        
        foreach ($posts as $post) {
            $date = $post->getPublishedAt() ?? $post->getCreatedAt();
            $dateStr = $date ? $date->format('d.m.Y') : 'Neznámy dátum';
            
            $categoryClass = 'default-gradient';
            $categoryName = '';
            if ($post->getCategory()) {
                $categoryName = htmlspecialchars($post->getCategory()->getName());
                $catLower = strtolower($categoryName);
                if (strpos($catLower, 'tech') !== false) {
                    $categoryClass = 'tech-gradient';
                } elseif (strpos($catLower, 'tutorial') !== false) {
                    $categoryClass = 'tutorial-gradient';
                } elseif (strpos($catLower, 'news') !== false) {
                    $categoryClass = 'news-gradient';
                }
            }
            
            $title = htmlspecialchars($post->getTitle());
            $slug = htmlspecialchars($post->getSlug());
            $excerpt = $post->getExcerpt() ? htmlspecialchars($post->getExcerpt()) : '';
            
            $html .= '
                <article class="portfolio-card blog-card">
                    <div class="portfolio-image blog-post-header">
                        ' . ($post->isFeatured() ? 
                            '<div class="image-label"><i class="fas fa-star me-1"></i>Odporúčané</div>' : 
                            '<div class="image-label"><i class="fas fa-blog me-1"></i>Blog Post</div>'
                        ) . '
                        <div class="gradient-overlay ' . $categoryClass . '"></div>
                    </div>
                    
                    <div class="portfolio-content">
                        <h3>
                            <a href="/blog/' . $slug . '">' . $title . '</a>
                        </h3>
                        
                        <p class="portfolio-tech">
                            <i class="fas fa-calendar me-1"></i>' . $dateStr . 
                            ($categoryName ? ' • ' . $categoryName : '') . '
                        </p>
                        
                        ' . ($excerpt ? '<p>' . $excerpt . '</p>' : '') . '
                        
                        ' . $this->renderTags($post) . '
                    </div>
                </article>
            ';
        }
        
        $html .= '</div>';
        
        // Add portfolio-grid CSS
        $html .= $this->getPortfolioCSS();
        
        return $html;
    }

    private function renderTags(\Orbit\Entity\Content $post): string
    {
        if (!$post->getTags()) {
            return '';
        }
        
        $html = '<div class="portfolio-stats">';
        $tags = array_slice($post->getTags(), 0, 3);
        
        foreach ($tags as $tag) {
            $html .= '<span class="stat"><i class="fas fa-tag me-1"></i>' . htmlspecialchars($tag->getName()) . '</span>';
        }
        
        if (count($post->getTags()) > 3) {
            $html .= '<span class="stat">+' . (count($post->getTags()) - 3) . ' ďalších</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    private function getPortfolioCSS(): string
    {
        return '
        <style>
        /* Portfolio Grid Layout */
        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .portfolio-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
        }

        .dark .portfolio-card {
            background: #1f2937;
            border-color: #374151;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .portfolio-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .portfolio-image {
            height: 200px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .gradient-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.8;
        }

        .default-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .tech-gradient { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .tutorial-gradient { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .news-gradient { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

        .image-label {
            position: absolute;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 2;
        }

        .portfolio-content {
            padding: 2rem;
        }

        .portfolio-content h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #111827;
            font-weight: 600;
        }

        .dark .portfolio-content h3 {
            color: #f9fafb;
        }

        .portfolio-content h3 a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .portfolio-content h3 a:hover {
            color: #3b82f6;
        }

        .portfolio-tech {
            color: #3b82f6;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .portfolio-content p {
            color: #6b7280;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        .dark .portfolio-content p {
            color: #d1d5db;
        }

        .portfolio-stats {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .stat {
            background: #f3f4f6;
            color: #111827;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid #e5e7eb;
        }

        .dark .stat {
            background: #374151;
            color: #f9fafb;
            border-color: #4b5563;
        }

        @media (max-width: 768px) {
            .portfolio-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .portfolio-content {
                padding: 1.5rem;
            }
        }
        </style>
        ';
    }
}
