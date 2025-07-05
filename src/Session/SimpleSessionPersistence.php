<?php

declare(strict_types=1);

namespace App\Session;

use Mezzio\Session\Session;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionPersistenceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Simple SessionPersistence implementation without mezzio-session-ext
 *
 * This provides basic session functionality using native PHP sessions
 * without the complexity of the full Session Ext package.
 */
class SimpleSessionPersistence implements SessionPersistenceInterface
{
    public function initializeSessionFromRequest(ServerRequestInterface $request): SessionInterface
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Create Session with current session data and session ID
        return new Session($_SESSION ?? [], $this->generateSessionId());
    }

    public function persistSession(SessionInterface $session, ResponseInterface $response): ResponseInterface
    {
        // If session has changed, update $_SESSION
        if ($session->hasChanged()) {
            $_SESSION = $session->toArray();
        }

        // If session was regenerated, regenerate PHP session ID
        if ($session->isRegenerated()) {
            session_regenerate_id(true);
        }

        return $response;
    }

    private function generateSessionId(): string
    {
        // Use existing session ID or generate new one
        if (session_status() === PHP_SESSION_ACTIVE) {
            $sessionId = session_id();
            return $sessionId !== false ? $sessionId : bin2hex(random_bytes(16));
        }

        return bin2hex(random_bytes(16));
    }
}
