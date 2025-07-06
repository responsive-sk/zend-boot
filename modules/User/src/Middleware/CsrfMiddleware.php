<?php

declare(strict_types=1);

namespace User\Middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if (!$session instanceof \Mezzio\Session\SessionInterface) {
            throw new \RuntimeException('Session middleware must be executed before CSRF middleware');
        }

        // Generate token for all requests
        $token = $this->generateToken();
        $this->storeToken($session, $token);
        $request = $request->withAttribute('csrf_token', $token);

        // Validate token for POST/PUT/DELETE requests
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $submittedToken = $this->getSubmittedToken($request);

            if (!$this->validateToken($session, $submittedToken)) {
                return new HtmlResponse('CSRF token validation failed', 403);
            }
        }

        return $handler->handle($request);
    }

    private function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function storeToken(\Mezzio\Session\SessionInterface $session, string $token): void
    {
        $tokens = $session->get('csrf_tokens', []);
        if (!is_array($tokens)) {
            $tokens = [];
        }
        $tokens[] = $token;

        // Keep only last 5 tokens to prevent memory issues
        if (count($tokens) > 5) {
            $tokens = array_slice($tokens, -5);
        }

        $session->set('csrf_tokens', $tokens);
    }

    private function getSubmittedToken(ServerRequestInterface $request): ?string
    {
        $parsedBody = $request->getParsedBody();

        // Check in POST data
        if (is_array($parsedBody) && isset($parsedBody['csrf_token']) && is_string($parsedBody['csrf_token'])) {
            return $parsedBody['csrf_token'];
        }

        // Check in headers
        $headerToken = $request->getHeaderLine('X-CSRF-Token');
        if ($headerToken !== '') {
            return $headerToken;
        }

        return null;
    }

    private function validateToken(\Mezzio\Session\SessionInterface $session, ?string $submittedToken): bool
    {
        if (!$submittedToken) {
            return false;
        }

        $storedTokens = $session->get('csrf_tokens', []);
        if (!is_array($storedTokens)) {
            return false;
        }

        foreach ($storedTokens as $index => $storedToken) {
            if (is_string($storedToken) && hash_equals($storedToken, $submittedToken)) {
                // Remove used token
                unset($storedTokens[$index]);
                $session->set('csrf_tokens', array_values($storedTokens));
                return true;
            }
        }

        return false;
    }

    /**
     * Helper method to get CSRF token for templates
     */
    public static function getToken(ServerRequestInterface $request): string
    {
        $token = $request->getAttribute('csrf_token', '');
        return is_string($token) ? $token : '';
    }
}
