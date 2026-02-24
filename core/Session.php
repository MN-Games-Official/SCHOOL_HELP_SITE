<?php

namespace Core;

class Session
{
    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function set(string $key, mixed $val): void
    {
        $_SESSION[$key] = $val;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }

        session_destroy();
    }

    public function flash(string $key, mixed $val): void
    {
        $_SESSION['_flash'][$key] = $val;
    }

    public function getFlash(string $key): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->set('_csrf_token', $token);
        return $token;
    }

    public function getCsrfToken(): string
    {
        if (!$this->has('_csrf_token')) {
            return $this->generateCsrfToken();
        }
        return $this->get('_csrf_token');
    }

    public function validateCsrfToken(string $token): bool
    {
        return hash_equals($this->get('_csrf_token', ''), $token);
    }
}
