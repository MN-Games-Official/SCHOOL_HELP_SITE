<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;

class HomeController
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

    public function index(): void
    {
        $courseStore = new JsonStore('courses.json');
        $userStore  = new JsonStore('users.json');

        $stats = [
            'total_courses'  => count($courseStore->readAll()),
            'total_students' => count(array_filter(
                $userStore->readAll(),
                fn(array $u) => ($u['role'] ?? '') === 'student'
            )),
            'total_teachers' => count(array_filter(
                $userStore->readAll(),
                fn(array $u) => ($u['role'] ?? '') === 'teacher'
            )),
        ];

        $features = [
            ['icon' => 'book',       'title' => 'Interactive Courses',  'description' => 'Access a wide range of courses with lessons, quizzes, and assignments.'],
            ['icon' => 'robot',      'title' => 'AI Tutor',             'description' => 'Get instant help from our AI-powered tutor on any subject.'],
            ['icon' => 'cards',      'title' => 'Flashcards & Notes',   'description' => 'Create flashcards and notes to boost your study sessions.'],
            ['icon' => 'chart',      'title' => 'Progress Tracking',    'description' => 'Track your grades and progress across all your courses.'],
            ['icon' => 'forum',      'title' => 'Discussion Forum',     'description' => 'Collaborate with peers and ask questions in the forum.'],
            ['icon' => 'calendar',   'title' => 'Calendar & Planner',   'description' => 'Stay organized with an integrated academic calendar.'],
        ];

        $data = [
            'title'    => 'Welcome to AIO Learning',
            'stats'    => $stats,
            'features' => $features,
            'user'     => $this->auth->user(),
        ];

        if ($this->auth->check()) {
            echo $this->view->layout('main', 'home/index', $data);
        } else {
            echo $this->view->layout('auth', 'home/index', $data);
        }
    }

    public function about(): void
    {
        $data = [
            'title' => 'About Us',
            'user'  => $this->auth->user(),
        ];

        if ($this->auth->check()) {
            echo $this->view->layout('main', 'home/about', $data);
        } else {
            echo $this->view->layout('auth', 'home/about', $data);
        }
    }

    public function contact(): void
    {
        $data = [
            'title'   => 'Contact Us',
            'user'    => $this->auth->user(),
            'success' => $this->session->getFlash('success'),
            'error'   => $this->session->getFlash('error'),
        ];

        if ($this->auth->check()) {
            echo $this->view->layout('main', 'home/contact', $data);
        } else {
            echo $this->view->layout('auth', 'home/contact', $data);
        }
    }

    public function contactSubmit(): void
    {
        $validator = new Validator();
        $data = $this->request->all();

        $validator->validate($data, [
            'name'    => 'required|min:2|max:100',
            'email'   => 'required|email',
            'subject' => 'required|min:3|max:200',
            'message' => 'required|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/contact');
            return;
        }

        $store = new JsonStore('contacts.json');
        $store->create([
            'name'    => trim($data['name']),
            'email'   => trim($data['email']),
            'subject' => trim($data['subject']),
            'message' => trim($data['message']),
            'status'  => 'new',
            'user_id' => $this->auth->check() ? $this->auth->user()['id'] : null,
        ]);

        $this->response->withSuccess('Thank you for your message! We will get back to you soon.');
        $this->response->redirect('/contact');
    }
}
