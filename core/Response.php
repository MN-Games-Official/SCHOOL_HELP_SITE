<?php

namespace Core;

class Response
{
    private Session $session;

    public function __construct(?Session $session = null)
    {
        $this->session = $session ?? new Session();
    }

    public function json(mixed $data, int $status = 200): void
    {
        $this->setStatusCode($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    public function back(): void
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referrer);
    }

    public function withError(string $msg): self
    {
        $this->session->flash('error', $msg);
        return $this;
    }

    public function withSuccess(string $msg): self
    {
        $this->session->flash('success', $msg);
        return $this;
    }

    public function setStatusCode(int $code): void
    {
        http_response_code($code);
    }
}
