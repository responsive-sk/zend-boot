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

    $app->get('/blog/{slug:.+}', [
        \Orbit\Handler\PostHandler::class,
    ], 'orbit.blog.post');

    // Category routes (TODO: implement handlers)
    // $app->get('/category/{slug:.+}', [\Orbit\Handler\CategoryHandler::class,], 'orbit.category.view');

    // Tag routes (TODO: implement handlers)
    // $app->get('/tag/{slug:.+}', [\Orbit\Handler\TagHandler::class,], 'orbit.tag.view');

    // Search routes (TODO: implement handlers)
    // $app->get('/search', [\Orbit\Handler\SearchHandler::class,], 'orbit.search');
    
    // Mark management routes (protected) - TODO: implement handlers
    // $app->route('/mark/orbit[/]', [
    //     \User\Middleware\RequireLoginMiddleware::class,
    //     \User\Middleware\RequireRoleMiddleware::class,
    //     \Orbit\Handler\MarkDashboardHandler::class,
    // ], ['GET'], 'orbit.mark.dashboard');
    
    $app->route('/mark/orbit/content[/]', [
        \User\Middleware\RequireLoginMiddleware::class,
        \User\Middleware\RequireRoleMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET'], 'orbit.mark.content.index');
    
    $app->route('/mark/orbit/content/{type:page|post|docs}[/]', [
        \User\Middleware\RequireLoginMiddleware::class,
        \User\Middleware\RequireRoleMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET'], 'orbit.mark.content.type');
    
    $app->route('/mark/orbit/content/{type:page|post|docs}/create', [
        \User\Middleware\RequireLoginMiddleware::class,
        \User\Middleware\RequireRoleMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET', 'POST'], 'orbit.mark.content.create');
    
    $app->route('/mark/orbit/content/{type:page|post|docs}/{id:\d+}/edit', [
        \User\Middleware\RequireLoginMiddleware::class,
        \User\Middleware\RequireRoleMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['GET', 'POST'], 'orbit.mark.content.edit');
    
    $app->route('/mark/orbit/content/{type:page|post|docs}/{id:\d+}/delete', [
        \User\Middleware\RequireLoginMiddleware::class,
        \User\Middleware\RequireRoleMiddleware::class,
        \Orbit\Handler\MarkContentHandler::class,
    ], ['POST'], 'orbit.mark.content.delete');
    
    // TODO: implement handlers
    // $app->route('/mark/orbit/editor', [
    //     \User\Middleware\RequireLoginMiddleware::class,
    //     \User\Middleware\RequireRoleMiddleware::class,
    //     \Orbit\Handler\MarkEditorHandler::class,
    // ], ['GET', 'POST'], 'orbit.mark.editor');

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
