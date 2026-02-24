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
use Services\CourseService;

class AdminController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private UserService $userService;
    private CourseService $courseService;

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

        $this->userService   = new UserService();
        $this->courseService = new CourseService();
    }

    public function index(): void
    {
        if (!$this->auth->isAdmin()) {
            $this->response->withError('Access denied.');
            $this->response->redirect('/dashboard');
            return;
        }

        $allUsers   = $this->userService->getAllUsers();
        $allCourses = $this->courseService->getAllCourses();

        $students = array_filter($allUsers, fn($u) => ($u['role'] ?? '') === 'student');
        $teachers = array_filter($allUsers, fn($u) => ($u['role'] ?? '') === 'teacher');
        $admins   = array_filter($allUsers, fn($u) => ($u['role'] ?? '') === 'admin');

        $activeCourses = array_filter($allCourses, fn($c) => ($c['status'] ?? '') === 'active');

        $submissionStore = new JsonStore('submissions.json');
        $quizAttemptStore = new JsonStore('quiz_attempts.json');

        $data = [
            'title'             => 'Admin Dashboard',
            'user'              => $this->auth->user(),
            'total_users'       => count($allUsers),
            'total_students'    => count($students),
            'total_teachers'    => count($teachers),
            'total_admins'      => count($admins),
            'total_courses'     => count($allCourses),
            'active_courses'    => count($activeCourses),
            'total_submissions' => count($submissionStore->readAll()),
            'total_attempts'    => count($quizAttemptStore->readAll()),
            'recent_users'      => array_slice(array_reverse($allUsers), 0, 10),
            'success'           => $this->session->getFlash('success'),
            'error'             => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'admin/dashboard', $data);
    }

    public function users(): void
    {
        if (!$this->auth->isAdmin()) {
            $this->response->withError('Access denied.');
            $this->response->redirect('/dashboard');
            return;
        }

        $search = $this->request->query('search', '');
        $role   = $this->request->query('role', '');

        if ($search !== '') {
            $users = $this->userService->searchUsers($search);
        } elseif ($role !== '') {
            $users = $this->userService->getUsersByRole($role);
        } else {
            $users = $this->userService->getAllUsers();
        }

        $data = [
            'title'   => 'Manage Users',
            'user'    => $this->auth->user(),
            'users'   => $users,
            'search'  => $search,
            'role'    => $role,
            'success' => $this->session->getFlash('success'),
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'admin/users', $data);
    }

    public function updateRole(string $id): void
    {
        if (!$this->auth->isAdmin()) {
            $this->response->withError('Access denied.');
            $this->response->redirect('/dashboard');
            return;
        }

        $currentUser = $this->auth->user();
        if ($id === $currentUser['id']) {
            $this->response->withError('You cannot change your own role.');
            $this->response->redirect('/admin/users');
            return;
        }

        $targetUser = $this->userService->findById($id);
        if (!$targetUser) {
            $this->response->withError('User not found.');
            $this->response->redirect('/admin/users');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'role' => 'required|in:student,teacher,admin',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Invalid role.');
            $this->response->redirect('/admin/users');
            return;
        }

        $this->userService->updateUser($id, ['role' => $input['role']]);

        $this->response->withSuccess(
            'Role for "' . View::escape($targetUser['name']) . '" updated to ' . $input['role'] . '.'
        );
        $this->response->redirect('/admin/users');
    }

    public function deleteUser(string $id): void
    {
        if (!$this->auth->isAdmin()) {
            $this->response->withError('Access denied.');
            $this->response->redirect('/dashboard');
            return;
        }

        $currentUser = $this->auth->user();
        if ($id === $currentUser['id']) {
            $this->response->withError('You cannot delete your own account.');
            $this->response->redirect('/admin/users');
            return;
        }

        $targetUser = $this->userService->findById($id);
        if (!$targetUser) {
            $this->response->withError('User not found.');
            $this->response->redirect('/admin/users');
            return;
        }

        $this->userService->deleteUser($id);

        $this->response->withSuccess('User "' . View::escape($targetUser['name']) . '" has been deleted.');
        $this->response->redirect('/admin/users');
    }

    public function settings(): void
    {
        if (!$this->auth->isAdmin()) {
            $this->response->withError('Access denied.');
            $this->response->redirect('/dashboard');
            return;
        }

        $settingsStore = new JsonStore('settings.json');
        $settings = $settingsStore->readAll();
        $currentSettings = !empty($settings) ? $settings[0] : [
            'site_name'           => 'AIO Learning',
            'site_description'    => 'All-in-One Learning Platform',
            'registration_open'   => true,
            'max_upload_size'     => 5,
            'maintenance_mode'    => false,
        ];

        $data = [
            'title'    => 'System Settings',
            'user'     => $this->auth->user(),
            'settings' => $currentSettings,
            'success'  => $this->session->getFlash('success'),
            'error'    => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'admin/settings', $data);
    }

    public function updateSettings(): void
    {
        if (!$this->auth->isAdmin()) {
            $this->response->withError('Access denied.');
            $this->response->redirect('/dashboard');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'site_name' => 'required|min:1|max:200',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/admin/settings');
            return;
        }

        $settingsStore = new JsonStore('settings.json');
        $existing = $settingsStore->readAll();

        $settingsData = [
            'site_name'          => trim($input['site_name']),
            'site_description'   => trim($input['site_description'] ?? ''),
            'registration_open'  => isset($input['registration_open']),
            'max_upload_size'    => max(1, (int) ($input['max_upload_size'] ?? 5)),
            'maintenance_mode'   => isset($input['maintenance_mode']),
        ];

        if (!empty($existing)) {
            $settingsStore->update($existing[0]['id'], $settingsData);
        } else {
            $settingsStore->create($settingsData);
        }

        $this->response->withSuccess('Settings updated successfully!');
        $this->response->redirect('/admin/settings');
    }
}
