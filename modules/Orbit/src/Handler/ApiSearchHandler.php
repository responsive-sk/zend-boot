<?php

declare(strict_types=1);

namespace Orbit\Handler;

use Orbit\Service\OrbitManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\JsonResponse;

/**
 * API Search Handler
 * 
 * API endpoint pre vyhľadávanie v obsahu.
 */
class ApiSearchHandler implements RequestHandlerInterface
{
    private OrbitManager $orbitManager;

    public function __construct(OrbitManager $orbitManager)
    {
        $this->orbitManager = $orbitManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $query = $queryParams['q'] ?? '';
        $type = $queryParams['type'] ?? null;
        $limit = (int) ($queryParams['limit'] ?? 20);
        
        // Validácia query
        if (strlen($query) < 3) {
            return new JsonResponse([
                'error' => 'Query must be at least 3 characters long',
                'results' => [],
                'total' => 0,
            ], 400);
        }
        
        try {
            // Vyhľadaj v obsahu
            $filters = [];
            if ($type) {
                $filters['type'] = $type;
            }
            $filters['limit'] = $limit;
            
            $results = $this->orbitManager->search($query, $filters);
            
            // Formátuj výsledky pre API
            $formattedResults = array_map(function($result) {
                return [
                    'id' => $result['id'],
                    'type' => $result['type'],
                    'title' => $result['title'],
                    'slug' => $result['slug'],
                    'url' => $this->buildUrl($result['type'], $result['slug']),
                    'excerpt' => $result['excerpt'] ?? '',
                    'snippet' => $result['snippet'] ?? '',
                    'category' => $result['category'] ?? null,
                    'tags' => $result['tags'] ?? [],
                    'updated_at' => $result['updated_at'] ?? null,
                ];
            }, $results);
            
            return new JsonResponse([
                'query' => $query,
                'results' => $formattedResults,
                'total' => count($formattedResults),
                'filters' => $filters,
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Search failed: ' . $e->getMessage(),
                'results' => [],
                'total' => 0,
            ], 500);
        }
    }

    private function buildUrl(string $type, string $slug): string
    {
        return match ($type) {
            'page' => "/page/{$slug}",
            'post' => "/blog/{$slug}",
            'docs' => "/docs/{$slug}",
            default => "/{$type}/{$slug}",
        };
    }
}
