<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;

class AuthController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;

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
    }

    public function loginForm(): void
    {
        $data = [
            'title'   => 'Sign In',
            'error'   => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success'),
        ];

        echo $this->view->layout('auth', 'auth/login', $data);
    }

    public function login(): void
    {
        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/login');
            return;
        }

        $email    = trim($input['email']);
        $password = $input['password'];

        if (!$this->auth->login($email, $password)) {
            $this->response->withError('Invalid email or password.');
            $this->response->redirect('/login');
            return;
        }

        $this->response->withSuccess('Welcome back!');
        $this->response->redirect('/dashboard');
    }

    public function registerForm(): void
    {
        $data = [
            'title' => 'Create Account',
            'error' => $this->session->getFlash('error'),
            'roles' => ['student', 'teacher'],
        ];

        echo $this->view->layout('auth', 'auth/register', $data);
    }

    public function register(): void
    {
        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'name'     => 'required|min:2|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:student,teacher',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/register');
            return;
        }

        $result = $this->auth->register([
            'name'     => trim($input['name']),
            'email'    => strtolower(trim($input['email'])),
            'password' => $input['password'],
            'role'     => $input['role'],
            'bio'      => '',
            'avatar'   => null,
            'active'   => true,
        ]);

        if ($result === false) {
            $this->response->withError('Registration failed. Please try again.');
            $this->response->redirect('/register');
            return;
        }

        $this->response->withSuccess('Account created successfully! Welcome aboard.');
        $this->response->redirect('/dashboard');
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->response->withSuccess('You have been logged out.');
        $this->response->redirect('/');
    }

    public function forgotPasswordForm(): void
    {
        $data = [
            'title'   => 'Forgot Password',
            'error'   => $this->session->getFlash('error'),
            'success' => $this->session->getFlash('success'),
        ];

        echo $this->view->layout('auth', 'auth/forgot-password', $data);
    }

    public function forgotPassword(): void
    {
        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Please enter a valid email.');
            $this->response->redirect('/forgot-password');
            return;
        }

        // Placeholder: in production this would send a reset email
        $this->response->withSuccess(
            'If an account with that email exists, a password reset link has been sent.'
        );
        $this->response->redirect('/forgot-password');
    }
}
