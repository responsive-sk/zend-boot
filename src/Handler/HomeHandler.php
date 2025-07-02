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
                <meta name="description" content="HDM Boot - Pokročilý Laminas Mezzio framework s theme systémom, versioned assets a production-ready optimalizáciami. Naše nové baby projekt! 💖">
                <meta name="keywords" content="HDM Boot, Laminas, Mezzio, PHP, Bootstrap, TailwindCSS, Alpine.js, theme system, responsive-sk, production-ready">
                <meta name="author" content="Responsive.sk - HDM Boot Team">
                <title>HDM Boot - Laminas Mezzio Theme System | Responsive.sk</title>
                <link rel="icon" href="/favicon.ico" type="image/x-icon">
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        min-height: 100vh;
                        color: #333;
                        line-height: 1.6;
                    }
                    .hero {
                        background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));
                        color: white;
                        padding: 80px 20px;
                        text-align: center;
                        position: relative;
                        overflow: hidden;
                    }
                    .hero::before {
                        content: "";
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: url("data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"0.1\"%3E%3Ccircle cx=\"30\" cy=\"30\" r=\"2\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
                        animation: float 20s ease-in-out infinite;
                    }
                    @keyframes float {
                        0%, 100% { transform: translateY(0px); }
                        50% { transform: translateY(-20px); }
                    }
                    .hero-content {
                        position: relative;
                        z-index: 1;
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    .hero h1 {
                        font-size: 3.5rem;
                        font-weight: 700;
                        margin-bottom: 20px;
                        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
                    }
                    .hero .subtitle {
                        font-size: 1.3rem;
                        margin-bottom: 30px;
                        opacity: 0.95;
                    }
                    .hero .tagline {
                        font-size: 1.1rem;
                        margin-bottom: 40px;
                        opacity: 0.9;
                        font-style: italic;
                    }
                    .cta-buttons {
                        display: flex;
                        gap: 20px;
                        justify-content: center;
                        flex-wrap: wrap;
                        margin-top: 40px;
                    }
                    .btn {
                        display: inline-block;
                        padding: 15px 30px;
                        border-radius: 50px;
                        text-decoration: none;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    }
                    .btn-primary {
                        background: #fff;
                        color: #667eea;
                        border: 2px solid #fff;
                    }
                    .btn-primary:hover {
                        background: transparent;
                        color: #fff;
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
                    }
                    .btn-secondary {
                        background: transparent;
                        color: #fff;
                        border: 2px solid #fff;
                    }
                    .btn-secondary:hover {
                        background: #fff;
                        color: #667eea;
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
                    }
                    .container {
                        max-width: 1200px;
                        margin: 0 auto;
                        padding: 60px 20px;
                    }
                    .features {
                        background: #fff;
                        border-radius: 20px;
                        padding: 60px 40px;
                        margin: -50px auto 60px;
                        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                        position: relative;
                        z-index: 2;
                    }
                    .features h2 {
                        text-align: center;
                        font-size: 2.5rem;
                        margin-bottom: 50px;
                        color: #333;
                    }
                    .feature-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                        gap: 40px;
                        margin-bottom: 50px;
                    }
                    .feature-card {
                        text-align: center;
                        padding: 30px;
                        border-radius: 15px;
                        background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
                        transition: transform 0.3s ease;
                    }
                    .feature-card:hover {
                        transform: translateY(-5px);
                    }
                    .feature-icon {
                        font-size: 3rem;
                        margin-bottom: 20px;
                        display: block;
                    }
                    .feature-card h3 {
                        font-size: 1.5rem;
                        margin-bottom: 15px;
                        color: #333;
                    }
                    .feature-card p {
                        color: #666;
                        line-height: 1.6;
                    }
                    .tech-stack {
                        background: #f8f9fa;
                        padding: 60px 40px;
                        border-radius: 20px;
                        text-align: center;
                    }
                    .tech-stack h2 {
                        font-size: 2.5rem;
                        margin-bottom: 30px;
                        color: #333;
                    }
                    .tech-categories {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                        gap: 40px;
                        margin: 40px 0;
                    }
                    .tech-category {
                        text-align: center;
                    }
                    .tech-category h3 {
                        font-size: 1.3rem;
                        margin-bottom: 20px;
                        color: #333;
                    }
                    .tech-badges {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 15px;
                        justify-content: center;
                    }
                    .tech-badge {
                        background: linear-gradient(135deg, #667eea, #764ba2);
                        color: white;
                        padding: 12px 24px;
                        border-radius: 25px;
                        font-weight: 600;
                        font-size: 0.9rem;
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                        transition: transform 0.3s ease;
                    }
                    .tech-badge:hover {
                        transform: scale(1.05);
                    }
                    .tech-badge.primary {
                        background: linear-gradient(135deg, #28a745, #20c997);
                        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
                    }
                    .tech-badge.coming-soon {
                        background: linear-gradient(135deg, #ffc107, #fd7e14);
                        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
                        position: relative;
                        animation: pulse 2s infinite;
                    }
                    @keyframes pulse {
                        0% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
                        50% { box-shadow: 0 4px 25px rgba(255, 193, 7, 0.6); }
                        100% { box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3); }
                    }
                    .github-links {
                        display: flex;
                        gap: 20px;
                        justify-content: center;
                        flex-wrap: wrap;
                        margin-top: 40px;
                    }
                    .github-link {
                        background: #24292e;
                        color: white;
                        padding: 20px 40px;
                        border-radius: 15px;
                        text-decoration: none;
                        display: inline-block;
                        margin-top: 30px;
                        font-weight: 600;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 15px rgba(36, 41, 46, 0.3);
                    }
                    .github-link:hover {
                        background: #333;
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(36, 41, 46, 0.4);
                    }
                    .github-link.secondary {
                        background: #667eea;
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
                    }
                    .github-link.secondary:hover {
                        background: #5a6fd8;
                        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
                    }
                    .footer {
                        text-align: center;
                        padding: 40px 20px;
                        color: white;
                        background: rgba(0,0,0,0.1);
                    }
                    .portfolio-section {
                        background: #fff;
                        border-radius: 20px;
                        padding: 60px 40px;
                        margin: 60px auto;
                        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                    }
                    .portfolio-section h2 {
                        text-align: center;
                        font-size: 2.5rem;
                        margin-bottom: 20px;
                        color: #333;
                    }
                    .portfolio-intro {
                        text-align: center;
                        font-size: 1.2rem;
                        color: #666;
                        margin-bottom: 50px;
                        font-style: italic;
                    }
                    .portfolio-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                        gap: 40px;
                        margin-bottom: 40px;
                    }
                    .portfolio-card {
                        background: #fff;
                        border-radius: 15px;
                        overflow: hidden;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                        transition: transform 0.3s ease, box-shadow 0.3s ease;
                        border: 1px solid #f0f0f0;
                    }
                    .portfolio-card:hover {
                        transform: translateY(-10px);
                        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
                    }
                    .portfolio-image {
                        position: relative;
                        height: 200px;
                        overflow: hidden;
                    }
                    .portfolio-image img {
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        transition: transform 0.3s ease;
                    }
                    .portfolio-card:hover .portfolio-image img {
                        transform: scale(1.05);
                    }
                    .coming-soon-overlay {
                        position: absolute;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: rgba(102, 126, 234, 0.9);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .coming-soon-badge {
                        background: #fff;
                        color: #667eea;
                        padding: 10px 20px;
                        border-radius: 25px;
                        font-weight: 700;
                        font-size: 1.1rem;
                        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                    }
                    .portfolio-content {
                        padding: 30px;
                    }
                    .portfolio-content h3 {
                        font-size: 1.5rem;
                        margin-bottom: 10px;
                        color: #333;
                    }
                    .portfolio-tech {
                        color: #667eea;
                        font-weight: 600;
                        font-size: 0.9rem;
                        margin-bottom: 15px;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .portfolio-content p {
                        color: #666;
                        line-height: 1.6;
                        margin-bottom: 20px;
                    }
                    .portfolio-stats {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 10px;
                    }
                    .stat {
                        background: linear-gradient(135deg, #f8f9ff, #f0f2ff);
                        color: #667eea;
                        padding: 6px 12px;
                        border-radius: 15px;
                        font-size: 0.8rem;
                        font-weight: 600;
                        border: 1px solid #e0e7ff;
                    }
                    .framework-comparison {
                        background: #f8f9fa;
                        border-radius: 20px;
                        padding: 60px 40px;
                        margin: 60px auto;
                    }
                    .framework-comparison h2 {
                        text-align: center;
                        font-size: 2.5rem;
                        margin-bottom: 50px;
                        color: #333;
                    }
                    .comparison-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
                        gap: 40px;
                    }
                    .comparison-card {
                        background: #fff;
                        border-radius: 15px;
                        padding: 40px;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                    }
                    .comparison-card.our-approach {
                        border-left: 5px solid #28a745;
                    }
                    .comparison-card.mainstream {
                        border-left: 5px solid #dc3545;
                    }
                    .comparison-card h3 {
                        font-size: 1.5rem;
                        margin-bottom: 30px;
                        text-align: center;
                    }
                    .our-approach h3 {
                        color: #28a745;
                    }
                    .mainstream h3 {
                        color: #dc3545;
                    }
                    .comparison-content {
                        display: flex;
                        flex-direction: column;
                        gap: 25px;
                    }
                    .approach-item {
                        padding: 20px;
                        border-radius: 10px;
                        background: #f8f9fa;
                    }
                    .approach-item strong {
                        display: block;
                        font-size: 1.1rem;
                        margin-bottom: 8px;
                        color: #333;
                    }
                    .approach-item p {
                        color: #666;
                        margin: 0;
                        line-height: 1.5;
                    }
                    @media (max-width: 768px) {
                        .hero h1 { font-size: 2.5rem; }
                        .hero .subtitle { font-size: 1.1rem; }
                        .cta-buttons { flex-direction: column; align-items: center; }
                        .features { margin: -30px 20px 40px; padding: 40px 20px; }
                        .feature-grid { grid-template-columns: 1fr; gap: 30px; }
                        .tech-stack { padding: 40px 20px; }
                        .portfolio-section { margin: 40px 20px; padding: 40px 20px; }
                        .portfolio-grid { grid-template-columns: 1fr; gap: 30px; }
                        .framework-comparison { margin: 40px 20px; padding: 40px 20px; }
                        .comparison-grid { grid-template-columns: 1fr; gap: 30px; }
                    }
                </style>
            </head>
            <body>
                <div class="hero">
                    <div class="hero-content">
                        <h1>🚀 HDM Boot</h1>
                        <p class="subtitle">Pokročilý Laminas Mezzio Framework</p>
                        <p class="tagline">Naše nové baby projekt s láskou vytvorený tímom Responsive.sk 💖</p>

                        <div class="cta-buttons">
                            <a href="/bootstrap-demo" class="btn btn-primary">Bootstrap Demo</a>
                            <a href="/main-demo" class="btn btn-secondary">TailwindCSS Demo</a>
                        </div>
                    </div>
                </div>

                <div class="container">
                    <div class="features">
                        <h2>🎯 Prečo HDM Boot?</h2>
                        <div class="feature-grid">
                            <div class="feature-card">
                                <span class="feature-icon">⚡</span>
                                <h3>Lightning Fast</h3>
                                <p>Optimalizované pre maximálny výkon s Vite build systémom a versioned assets pre long-term cache strategy.</p>
                            </div>
                            <div class="feature-card">
                                <span class="feature-icon">🎨</span>
                                <h3>Multi-Theme System</h3>
                                <p>Bootstrap 5.3 a TailwindCSS + Alpine.js témy s nezávislými build procesmi a secure asset managementom.</p>
                            </div>
                            <div class="feature-card">
                                <span class="feature-icon">🔒</span>
                                <h3>Production Ready</h3>
                                <p>Kompletná bezpečnostná konfigurácia, Apache .htaccess, CSP headers a 86% redukcia veľkosti buildu.</p>
                            </div>
                            <div class="feature-card">
                                <span class="feature-icon">📱</span>
                                <h3>Responsive Design</h3>
                                <p>Mobile-first prístup s perfektným Lighthouse skóre pre accessibility, SEO a performance.</p>
                            </div>
                            <div class="feature-card">
                                <span class="feature-icon">🛠️</span>
                                <h3>Developer Experience</h3>
                                <p>Kompletná dokumentácia, build scripty, hot reload a všetko potrebné pre produktívny development.</p>
                            </div>
                            <div class="feature-card">
                                <span class="feature-icon">🚀</span>
                                <h3>Easy Deployment</h3>
                                <p>Jeden príkaz pre production build, optimalizované pre CDN, gzip kompresiu a cache stratégie.</p>
                            </div>
                        </div>
                    </div>

                    <div class="portfolio-section">
                        <h2>🏆 Naše Framework Agnostic Portfolio</h2>
                        <p class="portfolio-intro">Zatiaľ čo ostatní sa učia jeden framework, my ovládame celý ekosystém!</p>

                        <div class="portfolio-grid">
                            <div class="portfolio-card">
                                <div class="portfolio-image">
                                    <img src="https://picsum.photos/400/250?random=1" alt="HDM Boot - Mezzio Framework" loading="lazy">
                                </div>
                                <div class="portfolio-content">
                                    <h3>🚀 HDM Boot</h3>
                                    <p class="portfolio-tech">Laminas Mezzio • Bootstrap • TailwindCSS</p>
                                    <p>Pokročilý full-stack framework pre enterprise aplikácie s multi-theme systémom a production-ready optimalizáciami.</p>
                                    <div class="portfolio-stats">
                                        <span class="stat">86% redukcia veľkosti</span>
                                        <span class="stat">0.5ms response</span>
                                        <span class="stat">Perfect Lighthouse</span>
                                    </div>
                                </div>
                            </div>

                            <div class="portfolio-card">
                                <div class="portfolio-image">
                                    <img src="https://picsum.photos/400/250?random=2" alt="Slim4 Stack - Micro Framework" loading="lazy">
                                </div>
                                <div class="portfolio-content">
                                    <h3>⚡ Slim4 Stack</h3>
                                    <p class="portfolio-tech">Slim Framework • APIs • Microservices</p>
                                    <p>Ultra-rýchly micro framework pre APIs a mikroslužby. Minimalistická krása s maximálnym výkonom.</p>
                                    <div class="portfolio-stats">
                                        <span class="stat">2MB footprint</span>
                                        <span class="stat">0.1ms response</span>
                                        <span class="stat">Serverless ready</span>
                                    </div>
                                </div>
                            </div>

                            <div class="portfolio-card coming-soon">
                                <div class="portfolio-image">
                                    <img src="https://picsum.photos/400/250?random=3" alt="Ephemeris Swiss Module" loading="lazy">
                                    <div class="coming-soon-overlay">
                                        <span class="coming-soon-badge">🇨🇭 Coming Soon</span>
                                    </div>
                                </div>
                                <div class="portfolio-content">
                                    <h3>🇨🇭 Ephemeris Swiss</h3>
                                    <p class="portfolio-tech">Swiss Precision • Advanced Algorithms • Enterprise</p>
                                    <p>Pripravujeme revolučný Swiss module s pokročilými algoritmami a švajčiarskou precíznosťou pre enterprise riešenia.</p>
                                    <div class="portfolio-stats">
                                        <span class="stat">Swiss Quality</span>
                                        <span class="stat">Advanced Algorithms</span>
                                        <span class="stat">Enterprise Grade</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="framework-comparison">
                        <h2>📊 Framework Agnostic Advantage</h2>
                        <div class="comparison-grid">
                            <div class="comparison-card our-approach">
                                <h3>💪 Náš Prístup</h3>
                                <div class="comparison-content">
                                    <div class="approach-item">
                                        <strong>Right Tool for the Job</strong>
                                        <p>Slim4 pre APIs, Mezzio pre full apps, Ephemeris pre enterprise</p>
                                    </div>
                                    <div class="approach-item">
                                        <strong>Performance First</strong>
                                        <p>18.6KB bundle vs 500KB+ mainstream</p>
                                    </div>
                                    <div class="approach-item">
                                        <strong>Cost Efficient</strong>
                                        <p>$5/mesiac VPS vs $200+ Vercel</p>
                                    </div>
                                    <div class="approach-item">
                                        <strong>Boring Technology Wins</strong>
                                        <p>PHP + Apache = 29 rokov stability</p>
                                    </div>
                                </div>
                            </div>

                            <div class="comparison-card mainstream">
                                <h3>😵 Mainstream Approach</h3>
                                <div class="comparison-content">
                                    <div class="approach-item">
                                        <strong>One Size Fits All</strong>
                                        <p>Next.js pre všetko, aj simple API</p>
                                    </div>
                                    <div class="approach-item">
                                        <strong>Bloated Bundles</strong>
                                        <p>500KB+ JavaScript pre "Hello World"</p>
                                    </div>
                                    <div class="approach-item">
                                        <strong>Vendor Lock-in</strong>
                                        <p>Vysoké hosting costs, závislosti</p>
                                    </div>
                                    <div class="approach-item">
                                        <strong>Hype-Driven Development</strong>
                                        <p>Rewrite každých 6 mesiacov</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tech-stack">
                        <h2>🔧 Framework Agnostic Tech Stack</h2>
                        <p>Postavené na najlepších technológiách pre moderný web development</p>

                        <div class="tech-categories">
                            <div class="tech-category">
                                <h3>🚀 Frameworks</h3>
                                <div class="tech-badges">
                                    <span class="tech-badge primary">Laminas Mezzio</span>
                                    <span class="tech-badge primary">Slim Framework</span>
                                    <span class="tech-badge coming-soon">Ephemeris Swiss 🇨🇭</span>
                                </div>
                            </div>

                            <div class="tech-category">
                                <h3>🎨 Frontend</h3>
                                <div class="tech-badges">
                                    <span class="tech-badge">Bootstrap 5.3</span>
                                    <span class="tech-badge">TailwindCSS 3.4</span>
                                    <span class="tech-badge">Alpine.js 3.14</span>
                                    <span class="tech-badge">Vite 5.4</span>
                                </div>
                            </div>

                            <div class="tech-category">
                                <h3>⚙️ Infrastructure</h3>
                                <div class="tech-badges">
                                    <span class="tech-badge">PHP 8.1+</span>
                                    <span class="tech-badge">Apache</span>
                                    <span class="tech-badge">Composer</span>
                                    <span class="tech-badge">PNPM</span>
                                </div>
                            </div>
                        </div>

                        <div class="github-links">
                            <a href="https://github.com/responsive-sk/hdm-boot" class="github-link" target="_blank" rel="noopener">
                                📦 HDM Boot Repository
                            </a>
                            <a href="https://github.com/responsive-sk" class="github-link secondary" target="_blank" rel="noopener">
                                🏢 Responsive.sk Organization
                            </a>
                        </div>
                    </div>
                </div>

                <div class="footer">
                    <p>Made with 💖 by <strong>Responsive.sk</strong> team | HDM Boot v2.0.0</p>
                    <p>Framework Agnostic • Swiss Precision 🇨🇭 • Production Ready</p>
                    <p style="margin-top: 15px; font-size: 0.9rem; opacity: 0.8;">
                        HDM Boot • Slim4 Stack • Ephemeris Swiss (Coming Soon)
                    </p>
                </div>
            </body>
            </html>
        ');
    }
}
