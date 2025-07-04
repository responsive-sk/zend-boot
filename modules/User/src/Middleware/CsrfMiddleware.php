<?php

declare(strict_types=1);

namespace User\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Csrf\CsrfGuardInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CsrfGuardInterface $guard
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Generate token for all requests
        $token = $this->guard->generateToken();
        $request = $request->withAttribute('csrf_token', $token);

        // Validate token for POST/PUT/DELETE requests
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $submittedToken = $this->getSubmittedToken($request);

            if (!$this->guard->validateToken($submittedToken)) {
                return new HtmlResponse('CSRF token validation failed', 403);
            }
        }

        return $handler->handle($request);
    }

    private function getSubmittedToken(ServerRequestInterface $request): ?string
    {
        $parsedBody = $request->getParsedBody();

        // Check in POST data
        if (is_array($parsedBody) && isset($parsedBody['csrf_token'])) {
            return $parsedBody['csrf_token'];
        }

        // Check in headers
        $headerToken = $request->getHeaderLine('X-CSRF-Token');
        if ($headerToken) {
            return $headerToken;
        }

        return null;
    }

    /**
     * Helper method to get CSRF token for templates
     */
    public static function getToken(ServerRequestInterface $request): string
    {
        return $request->getAttribute('csrf_token', '');
    }
}
