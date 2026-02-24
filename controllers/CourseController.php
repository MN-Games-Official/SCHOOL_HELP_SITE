<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\CourseService;
use Services\LessonService;

class CourseController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private CourseService $courseService;
    private LessonService $lessonService;

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

        $this->courseService = new CourseService();
        $this->lessonService = new LessonService();
    }

    public function index(): void
    {
        $user    = $this->auth->user();
        $search  = $this->request->query('search', '');
        $subject = $this->request->query('subject', '');

        if ($search !== '') {
            $courses = $this->courseService->searchCourses($search);
        } else {
            $courses = $this->courseService->getAllCourses();
        }

        // Filter by subject if provided
        if ($subject !== '') {
            $courses = array_values(array_filter(
                $courses,
                fn(array $c) => ($c['subject'] ?? '') === $subject
            ));
        }

        // Collect unique subjects for filter dropdown
        $allCourses = $this->courseService->getAllCourses();
        $subjects = array_unique(array_filter(array_column($allCourses, 'subject')));
        sort($subjects);

        $data = [
            'title'    => 'Courses',
            'user'     => $user,
            'courses'  => $courses,
            'search'   => $search,
            'subject'  => $subject,
            'subjects' => $subjects,
            'success'  => $this->session->getFlash('success'),
            'error'    => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'courses/index', $data);
    }

    public function show(string $id): void
    {
        $user   = $this->auth->user();
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            $this->response->withError('Course not found.');
            $this->response->redirect('/courses');
            return;
        }

        $lessons    = $this->lessonService->getLessonsByCourse($id);
        $isEnrolled = $user ? $this->courseService->isEnrolled($id, $user['id']) : false;
        $stats      = $this->courseService->getCourseStats($id);

        $data = [
            'title'       => $course['title'],
            'user'        => $user,
            'course'      => $course,
            'lessons'     => $lessons,
            'is_enrolled' => $isEnrolled,
            'stats'       => $stats,
            'success'     => $this->session->getFlash('success'),
            'error'       => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'courses/show', $data);
    }

    public function create(): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can create courses.');
            $this->response->redirect('/courses');
            return;
        }

        $data = [
            'title'   => 'Create Course',
            'user'    => $this->auth->user(),
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'courses/create', $data);
    }

    public function store(): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can create courses.');
            $this->response->redirect('/courses');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'       => 'required|min:3|max:200',
            'description' => 'required|min:10|max:5000',
            'subject'     => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/courses/create');
            return;
        }

        $user = $this->auth->user();

        $course = $this->courseService->createCourse([
            'title'       => $input['title'],
            'description' => $input['description'],
            'subject'     => $input['subject'],
            'teacher_id'  => $user['id'],
            'status'      => $input['status'] ?? 'active',
        ]);

        $this->response->withSuccess('Course created successfully!');
        $this->response->redirect('/courses/' . $course['id']);
    }

    public function edit(string $id): void
    {
        $user   = $this->auth->user();
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            $this->response->withError('Course not found.');
            $this->response->redirect('/courses');
            return;
        }

        if ($course['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only edit your own courses.');
            $this->response->redirect('/courses/' . $id);
            return;
        }

        $data = [
            'title'  => 'Edit Course',
            'user'   => $user,
            'course' => $course,
            'error'  => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'courses/edit', $data);
    }

    public function update(string $id): void
    {
        $user   = $this->auth->user();
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            $this->response->withError('Course not found.');
            $this->response->redirect('/courses');
            return;
        }

        if ($course['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only edit your own courses.');
            $this->response->redirect('/courses/' . $id);
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'       => 'required|min:3|max:200',
            'description' => 'required|min:10|max:5000',
            'subject'     => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/courses/' . $id . '/edit');
            return;
        }

        $this->courseService->updateCourse($id, [
            'title'       => $input['title'],
            'description' => $input['description'],
            'subject'     => $input['subject'],
            'status'      => $input['status'] ?? $course['status'],
        ]);

        $this->response->withSuccess('Course updated successfully!');
        $this->response->redirect('/courses/' . $id);
    }

    public function delete(string $id): void
    {
        $user   = $this->auth->user();
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            $this->response->withError('Course not found.');
            $this->response->redirect('/courses');
            return;
        }

        if ($course['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only delete your own courses.');
            $this->response->redirect('/courses/' . $id);
            return;
        }

        $this->courseService->deleteCourse($id);

        $this->response->withSuccess('Course deleted successfully.');
        $this->response->redirect('/courses');
    }

    public function enroll(string $id): void
    {
        $user   = $this->auth->user();
        $course = $this->courseService->getCourseById($id);

        if (!$course) {
            $this->response->withError('Course not found.');
            $this->response->redirect('/courses');
            return;
        }

        if ($this->courseService->isEnrolled($id, $user['id'])) {
            $this->response->withError('You are already enrolled in this course.');
            $this->response->redirect('/courses/' . $id);
            return;
        }

        $this->courseService->enrollStudent($id, $user['id']);

        $this->response->withSuccess('You have been enrolled in "' . View::escape($course['title']) . '"!');
        $this->response->redirect('/courses/' . $id);
    }
}
