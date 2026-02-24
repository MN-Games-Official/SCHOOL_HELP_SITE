<?php

namespace Core;

class Middleware
{
    private Auth $auth;
    private Session $session;

    public function __construct(Auth $auth, Session $session)
    {
        $this->auth    = $auth;
        $this->session = $session;
    }

    public function auth(): bool
    {
        if (!$this->auth->check()) {
            $this->session->flash('error', 'Please log in to continue.');
            header('Location: /login');
            exit;
        }
        return true;
    }

    public function guest(): bool
    {
        if ($this->auth->check()) {
            header('Location: /dashboard');
            exit;
        }
        return true;
    }

    public function teacher(): bool
    {
        $this->auth();

        if (!$this->auth->isTeacher()) {
            $this->session->flash('error', 'Access denied. Teachers only.');
            header('Location: /dashboard');
            exit;
        }
        return true;
    }

    public function admin(): bool
    {
        $this->auth();

        if (!$this->auth->isAdmin()) {
            $this->session->flash('error', 'Access denied. Admins only.');
            header('Location: /dashboard');
            exit;
        }
        return true;
    }

    public function csrf(): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            return true;
        }

        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!$this->session->validateCsrfToken($token)) {
            $this->session->flash('error', 'Invalid CSRF token. Please try again.');
            $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
            header("Location: {$referrer}");
            exit;
        }

        return true;
    }
}
