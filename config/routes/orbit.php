<?php

declare(strict_types=1);

use Mezzio\Application;
use Psr\Container\ContainerInterface;

/**
 * Orbit CMS Routes
 * 
 * Routes pre Orbit CMS - dokumentácia, stránky, blog, API.
 */
return static function (Application $app, ContainerInterface $container): void {
    
    // Documentation routes
    $app->get('/docs[/]', [
        \Orbit\Handler\DocsHandler::class,
    ], 'orbit.docs.index');
    
    $app->get('/docs/{lang:sk|en}[/]', [
        \Orbit\Handler\DocsHandler::class,
    ], 'orbit.docs.lang_index');
    
    $app->get('/docs/{lang:sk|en}/{slug:.+}', [
        \Orbit\Handler\DocsHandler::class,
    ], 'orbit.docs.view');
    
    // Page routes
    $app->get('/page/{slug:.+}', [
        \Orbit\Handler\PageHandler::class,
    ], 'orbit.page.view');
    
    // Blog routes
    $app->get('/blog[/]', [
        \Orbit\Handler\BlogHandler::class,
    ], 'orbit.blog.index');

    // Blog with Tailwind theme
    $app->get('/blog-tailwind[/]', [
        \Orbit\Handler\BlogTailwindHandler::class,
    ], 'orbit.blog.tailwind');

    $app->get('/blog/{slug:.+}', [
        \Orbit\Handler\PostHandler::class,
    ], 'orbit.blog.post');



    // Category routes (TODO: implement handlers)
    // $app->get('/category/{slug:.+}', [\Orbit\Handler\CategoryHandler::class,], 'orbit.category.view');

    // Tag routes (TODO: implement handlers)
    // $app->get('/tag/{slug:.+}', [\Orbit\Handler\TagHandler::class,], 'orbit.tag.view');

    // Search routes (TODO: implement handlers)
    // $app->get('/search', [\Orbit\Handler\SearchHandler::class,], 'orbit.search');
    
    // Mark management routes (protected) - using Mark authentication
    $app->route('/mark/orbit[/]', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkDashboardHandler::class,
    ], ['GET'], 'orbit.mark.dashboard');

    $app->route('/mark/orbit/content[/]', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET'], 'orbit.mark.content.index');

    $app->route('/mark/orbit/content/{type:page|post|docs}[/]', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET'], 'orbit.mark.content.type');

    $app->route('/mark/orbit/content/create', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET', 'POST'], 'orbit.mark.content.create.general');

    $app->route('/mark/orbit/content/{type:page|post|docs}/create', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET', 'POST'], 'orbit.mark.content.create');

    $app->route('/mark/orbit/content/{type:page|post|docs}/{id:\d+}/edit', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET', 'POST'], 'orbit.mark.content.edit');

    $app->route('/mark/orbit/content/{type:page|post|docs}/{id:\d+}/delete', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['POST'], 'orbit.mark.content.delete');
    
    // Advanced Editor
    $app->route('/mark/orbit/editor[/]', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkEditorHandler::class,
    ], ['GET'], 'orbit.mark.editor');

    $app->route('/mark/orbit/editor/{id:\d+}', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkEditorHandler::class,
    ], ['GET'], 'orbit.mark.editor.edit');

    $app->route('/mark/orbit/editor/preview', [
        \Mark\Middleware\MarkAuthenticationMiddleware::class,
        \Orbit\Handler\MarkEditorHandler::class,
    ], ['POST'], 'orbit.mark.editor.preview');

    // $app->route('/mark/orbit/media[/]', [
    //     \User\Middleware\RequireLoginMiddleware::class,
    //     \User\Middleware\RequireRoleMiddleware::class,
    //     \Orbit\Handler\MarkMediaHandler::class,
    // ], ['GET', 'POST'], 'orbit.mark.media');
    
    // API routes
    $app->get('/api/orbit/search', [
        \Orbit\Handler\ApiSearchHandler::class,
    ], 'orbit.api.search');
    
    // TODO: implement API handlers
    // $app->get('/api/orbit/content/{type:page|post|docs}[/]', [\Orbit\Handler\ApiContentHandler::class,], 'orbit.api.content.list');
    // $app->get('/api/orbit/content/{type:page|post|docs}/{slug:.+}', [\Orbit\Handler\ApiContentHandler::class,], 'orbit.api.content.view');
    // $app->get('/api/orbit/categories[/]', [\Orbit\Handler\ApiCategoryHandler::class,], 'orbit.api.categories');
    // $app->get('/api/orbit/tags[/]', [\Orbit\Handler\ApiTagHandler::class,], 'orbit.api.tags');

    // Mark API routes (protected) - TODO: implement handlers
    // $app->route('/api/orbit/mark/content', [
    //     \User\Middleware\RequireLoginMiddleware::class,
    //     \User\Middleware\RequireRoleMiddleware::class,
    //     \Orbit\Handler\ApiMarkHandler::class,
    // ], ['GET', 'POST'], 'orbit.api.mark.content');

    // $app->route('/api/orbit/mark/content/{id:\d+}', [
    //     \User\Middleware\RequireLoginMiddleware::class,
    //     \User\Middleware\RequireRoleMiddleware::class,
    //     \Orbit\Handler\ApiMarkHandler::class,
    // ], ['GET', 'PUT', 'DELETE'], 'orbit.api.mark.content.item');
};
