<?php

namespace Core;

class Auth
{
    private Session $session;
    private mixed $userService;

    public function __construct(Session $session, $userService)
    {
        $this->session     = $session;
        $this->userService = $userService;
    }

    public function login(string $email, string $password): bool
    {
        $users = $this->userService->findByEmail($email);

        if (!$users) {
            return false;
        }

        if (is_array($users) && isset($users['id'])) {
            $user = $users;
        } elseif (is_array($users) && !empty($users)) {
            $user = $users[0];
        } else {
            return false;
        }

        if (!$this->verifyPassword($password, $user['password'] ?? '')) {
            return false;
        }

        $this->session->set('user_id', $user['id']);
        $this->session->set('user', $user);

        return true;
    }

    public function register(array $data): array|false
    {
        $data['password'] = $this->hashPassword($data['password']);

        $user = $this->userService->create($data);

        if ($user) {
            $this->session->set('user_id', $user['id']);
            $this->session->set('user', $user);
        }

        return $user ?: false;
    }

    public function logout(): void
    {
        $this->session->remove('user_id');
        $this->session->remove('user');
        $this->session->destroy();
    }

    public function user(): ?array
    {
        return $this->session->get('user');
    }

    public function check(): bool
    {
        return $this->session->has('user_id');
    }

    public function isTeacher(): bool
    {
        $user = $this->user();
        return $user && ($user['role'] ?? '') === 'teacher';
    }

    public function isStudent(): bool
    {
        $user = $this->user();
        return $user && ($user['role'] ?? '') === 'student';
    }

    public function isAdmin(): bool
    {
        $user = $this->user();
        return $user && ($user['role'] ?? '') === 'admin';
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
