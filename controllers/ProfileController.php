<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\UserService;

class ProfileController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private UserService $userService;

    public function __construct()
    {
        $this->session  = new Session();
        $this->view     = new View();
        $this->request  = new Request();
        $this->response = new Response($this->session);

        $userStore  = new JsonStore('users.json');
        $this->auth = new Auth($this->session, new class($userStore) {
            private $s;
            public function __construct($s) { $this->s = $s; }
            public function findByEmail(string $e): ?array { $r = $this->s->findBy('email', $e); return $r[0] ?? null; }
            public function create(array $d): array { return $this->s->create($d); }
        });

        $this->userService = new UserService();
    }

    public function show(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->withError('Please log in to view your profile.');
            $this->response->redirect('/login');
            return;
        }

        // Get fresh user data from the store
        $userData = $this->userService->findById($user['id']);
        if (!$userData) {
            $this->response->withError('User not found.');
            $this->response->redirect('/dashboard');
            return;
        }

        $stats = $this->userService->getStats($user['id']);

        $data = [
            'title'   => 'My Profile',
            'user'    => $userData,
            'stats'   => $stats,
            'success' => $this->session->getFlash('success'),
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'profile/show', $data);
    }

    public function edit(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->withError('Please log in.');
            $this->response->redirect('/login');
            return;
        }

        $userData = $this->userService->findById($user['id']);

        $data = [
            'title' => 'Edit Profile',
            'user'  => $userData ?? $user,
            'error' => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'profile/edit', $data);
    }

    public function update(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->withError('Please log in.');
            $this->response->redirect('/login');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'name'  => 'required|min:2|max:100',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/profile/edit');
            return;
        }

        $updateData = [
            'name'  => trim($input['name']),
            'email' => strtolower(trim($input['email'])),
            'bio'   => trim($input['bio'] ?? ''),
        ];

        // Handle avatar upload
        $avatar = $this->request->file('avatar');
        if ($avatar && $avatar['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($avatar['type'], $allowedTypes, true)) {
                $this->response->withError('Avatar must be a JPEG, PNG, GIF, or WebP image.');
                $this->response->redirect('/profile/edit');
                return;
            }

            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($avatar['size'] > $maxSize) {
                $this->response->withError('Avatar must be smaller than 2MB.');
                $this->response->redirect('/profile/edit');
                return;
            }

            $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
            $filename = $user['id'] . '_' . time() . '.' . $ext;
            $uploadDir = BASE_PATH . '/storage/avatars/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destination = $uploadDir . $filename;
            if (move_uploaded_file($avatar['tmp_name'], $destination)) {
                $updateData['avatar'] = '/storage/avatars/' . $filename;
            }
        }

        try {
            $updatedUser = $this->userService->updateUser($user['id'], $updateData);
        } catch (\RuntimeException $e) {
            $this->response->withError($e->getMessage());
            $this->response->redirect('/profile/edit');
            return;
        }

        if ($updatedUser) {
            // Refresh session data
            $this->session->set('user', $updatedUser);
        }

        $this->response->withSuccess('Profile updated successfully!');
        $this->response->redirect('/profile');
    }
}
