<?php

declare(strict_types=1);

namespace Orbit\Service;

use Orbit\Entity\Content;
use Orbit\Entity\Category;
use Orbit\Entity\Tag;
use Orbit\Service\FileDriver\FileDriverInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Orbit Manager
 * 
 * Hlavná služba pre správu obsahu v Orbit CMS.
 */
class OrbitManager
{
    private array $config;
    private ContentRepository $contentRepository;
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private SearchService $searchService;
    private array $drivers = [];

    public function __construct(
        array $config,
        ContentRepository $contentRepository,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        SearchService $searchService,
        array $drivers = []
    ) {
        $this->config = $config;
        $this->contentRepository = $contentRepository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->searchService = $searchService;
        $this->drivers = $drivers;
    }

    // Content management
    public function findContent(string $type, string $slug): ?Content
    {
        return $this->contentRepository->findByTypeAndSlug($type, $slug);
    }

    public function findContentById(int $id): ?Content
    {
        return $this->contentRepository->findById($id);
    }

    public function getAllContent(?string $type = null, bool $publishedOnly = true): array
    {
        return $this->contentRepository->findAll($type, $publishedOnly);
    }

    public function getFeaturedContent(?string $type = null, int $limit = 5): array
    {
        return $this->contentRepository->findFeatured($type, $limit);
    }

    public function getContentByCategory(int $categoryId, bool $publishedOnly = true): array
    {
        return $this->contentRepository->findByCategory($categoryId, $publishedOnly);
    }

    public function getContentByTag(int $tagId, bool $publishedOnly = true): array
    {
        return $this->contentRepository->findByTag($tagId, $publishedOnly);
    }

    public function createContent(string $type, array $data): Content
    {
        $this->validateContentType($type);

        $slug = $data['slug'] ?? $this->generateSlug($data['title']);

        // Check if slug already exists
        if ($this->slugExists($type, $slug)) {
            $slug = $this->generateUniqueSlug($type, $slug);
        }

        $filePath = $this->generateFilePath($type, $slug);
        
        $content = new Content($type, $slug, $data['title'], $filePath);
        
        // Set optional properties
        if (isset($data['published'])) {
            $content->setPublished((bool) $data['published']);
        }
        
        if (isset($data['featured'])) {
            $content->setFeatured((bool) $data['featured']);
        }
        
        if (isset($data['category_id'])) {
            $content->setCategoryId((int) $data['category_id']);
        }
        
        if (isset($data['meta_data'])) {
            $content->setMetaData($data['meta_data']);
        }
        
        // Save to database
        $this->contentRepository->save($content);
        
        // Create file
        $this->writeContentFile($content, $data['body'] ?? '');
        
        // Index for search
        $this->searchService->indexContent($content);
        
        return $content;
    }

    public function updateContent(Content $content, array $data): bool
    {
        // Update properties
        if (isset($data['title'])) {
            $content->setTitle($data['title']);
        }
        
        if (isset($data['slug'])) {
            $oldFilePath = $content->getFilePath();
            $content->setSlug($data['slug']);
            $newFilePath = $this->generateFilePath($content->getType(), $data['slug']);
            $content->setFilePath($newFilePath);
            
            // Rename file if slug changed
            if ($oldFilePath !== $newFilePath) {
                $this->moveContentFile($oldFilePath, $newFilePath);
            }
        }
        
        if (isset($data['published'])) {
            $content->setPublished((bool) $data['published']);
        }
        
        if (isset($data['featured'])) {
            $content->setFeatured((bool) $data['featured']);
        }
        
        if (isset($data['category_id'])) {
            $content->setCategoryId($data['category_id'] ? (int) $data['category_id'] : null);
        }
        
        if (isset($data['meta_data'])) {
            $content->setMetaData($data['meta_data']);
        }
        
        // Update file content
        if (isset($data['body'])) {
            $this->writeContentFile($content, $data['body']);
        }
        
        // Save to database
        $this->contentRepository->save($content);
        
        // Reindex for search
        $this->searchService->indexContent($content);
        
        return true;
    }

    public function deleteContent(Content $content): bool
    {
        // Delete file
        $this->deleteContentFile($content);
        
        // Remove from search index
        $this->searchService->removeFromIndex($content);
        
        // Delete from database
        return $this->contentRepository->delete($content);
    }

    public function loadContentFromFile(Content $content): Content
    {
        $driver = $this->getDriverForContent($content);
        $fileData = $driver->read($content->getFilePath());
        
        $content->setRawContent($fileData['content']);
        $content->setRenderedContent($driver->render($fileData['content']));
        
        if (!empty($fileData['meta'])) {
            $content->setMetaData(array_merge($content->getMetaData(), $fileData['meta']));
        }
        
        return $content;
    }

    // Category management
    public function findCategory(string $slug): ?Category
    {
        return $this->categoryRepository->findBySlug($slug);
    }

    public function findCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    public function getAllCategories(bool $activeOnly = true): array
    {
        return $this->categoryRepository->findAll($activeOnly);
    }

    public function getCategoryTree(): array
    {
        return $this->categoryRepository->getTree();
    }

    // Tag management
    public function findTag(string $slug): ?Tag
    {
        return $this->tagRepository->findBySlug($slug);
    }

    public function findTagById(int $id): ?Tag
    {
        return $this->tagRepository->findById($id);
    }

    public function getAllTags(): array
    {
        return $this->tagRepository->findAll();
    }

    public function getPopularTags(int $limit = 20): array
    {
        return $this->tagRepository->findPopular($limit);
    }

    // Search
    public function search(string $query, array $filters = []): array
    {
        return $this->searchService->search($query, $filters);
    }

    // Helper methods
    private function validateContentType(string $type): void
    {
        if (!isset($this->config['content_types'][$type])) {
            throw new InvalidArgumentException("Unknown content type: $type");
        }
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug) ?? $slug;
        $slug = preg_replace('/[\s-]+/', '-', $slug) ?? $slug;
        return trim($slug, '-');
    }

    private function slugExists(string $type, string $slug): bool
    {
        $content = $this->contentRepository->findByTypeAndSlug($type, $slug);
        return $content !== null;
    }

    private function generateUniqueSlug(string $type, string $baseSlug): string
    {
        $counter = 1;
        $slug = $baseSlug;

        while ($this->slugExists($type, $slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateFilePath(string $type, string $slug): string
    {
        $typeConfig = $this->config['content_types'][$type];
        $basePath = $this->config['content_path'] . '/' . $typeConfig['path'];
        return $basePath . '/' . $slug . '.md';
    }

    private function getDriverForContent(Content $content): FileDriverInterface
    {
        $typeConfig = $this->config['content_types'][$content->getType()];
        $driverName = $typeConfig['driver'] ?? $this->config['default_driver'];
        
        if (!isset($this->drivers[$driverName])) {
            throw new RuntimeException("Driver not found: $driverName");
        }
        
        return $this->drivers[$driverName];
    }

    private function writeContentFile(Content $content, string $body): void
    {
        $driver = $this->getDriverForContent($content);
        $driver->write($content->getFilePath(), [
            'meta' => $content->getMetaData(),
            'content' => $body,
        ]);
        
        $content->setRawContent($body);
        $content->setContentHash(hash('sha256', $body));
    }

    private function moveContentFile(string $oldPath, string $newPath): void
    {
        $fullOldPath = $oldPath;
        $fullNewPath = $newPath;
        
        if (file_exists($fullOldPath)) {
            $newDir = dirname($fullNewPath);
            if (!is_dir($newDir)) {
                mkdir($newDir, 0755, true);
            }
            rename($fullOldPath, $fullNewPath);
        }
    }

    private function deleteContentFile(Content $content): void
    {
        $filePath = $content->getFilePath();
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
