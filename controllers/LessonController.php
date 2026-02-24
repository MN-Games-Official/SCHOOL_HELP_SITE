<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\LessonService;
use Services\CourseService;

class LessonController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private LessonService $lessonService;
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

        $this->lessonService = new LessonService();
        $this->courseService = new CourseService();
    }

    public function show(string $id): void
    {
        $user   = $this->auth->user();
        $lesson = $this->lessonService->getLessonById($id);

        if (!$lesson) {
            $this->response->withError('Lesson not found.');
            $this->response->redirect('/courses');
            return;
        }

        $courseId = $lesson['course_id'];
        $course   = $this->courseService->getCourseById($courseId);

        if (!$course) {
            $this->response->withError('Associated course not found.');
            $this->response->redirect('/courses');
            return;
        }

        // Build prev/next navigation
        $allLessons = $this->lessonService->getLessonsByCourse($courseId);
        $currentIdx = null;
        foreach ($allLessons as $i => $l) {
            if ($l['id'] === $id) {
                $currentIdx = $i;
                break;
            }
        }

        $prevLesson = ($currentIdx !== null && $currentIdx > 0) ? $allLessons[$currentIdx - 1] : null;
        $nextLesson = ($currentIdx !== null && $currentIdx < count($allLessons) - 1) ? $allLessons[$currentIdx + 1] : null;

        // Mark lesson as complete for the student
        if ($user) {
            $this->lessonService->markAsComplete($id, $user['id']);
        }

        $progress = $user ? $this->lessonService->getProgress($courseId, $user['id']) : null;

        $data = [
            'title'       => $lesson['title'],
            'user'        => $user,
            'lesson'      => $lesson,
            'course'      => $course,
            'prev_lesson' => $prevLesson,
            'next_lesson' => $nextLesson,
            'progress'    => $progress,
            'all_lessons' => $allLessons,
            'success'     => $this->session->getFlash('success'),
            'error'       => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'lessons/show', $data);
    }

    public function create(string $courseId): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can create lessons.');
            $this->response->redirect('/courses/' . $courseId);
            return;
        }

        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            $this->response->withError('Course not found.');
            $this->response->redirect('/courses');
            return;
        }

        $user = $this->auth->user();
        if ($course['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only add lessons to your own courses.');
            $this->response->redirect('/courses/' . $courseId);
            return;
        }

        $data = [
            'title'  => 'Create Lesson',
            'user'   => $user,
            'course' => $course,
            'error'  => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'lessons/create', $data);
    }

    public function store(string $courseId): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can create lessons.');
            $this->response->redirect('/courses/' . $courseId);
            return;
        }

        $course = $this->courseService->getCourseById($courseId);
        if (!$course) {
            $this->response->withError('Course not found.');
            $this->response->redirect('/courses');
            return;
        }

        $user = $this->auth->user();
        if ($course['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only add lessons to your own courses.');
            $this->response->redirect('/courses/' . $courseId);
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'   => 'required|min:3|max:200',
            'content' => 'required|min:10',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/courses/' . $courseId . '/lessons/create');
            return;
        }

        $lesson = $this->lessonService->createLesson([
            'course_id'  => $courseId,
            'title'      => $input['title'],
            'content'    => $input['content'],
            'type'       => $input['type'] ?? 'text',
            'video_url'  => $input['video_url'] ?? null,
            'sort_order' => isset($input['sort_order']) ? (int) $input['sort_order'] : null,
        ]);

        $this->response->withSuccess('Lesson created successfully!');
        $this->response->redirect('/lessons/' . $lesson['id']);
    }

    public function edit(string $id): void
    {
        $user   = $this->auth->user();
        $lesson = $this->lessonService->getLessonById($id);

        if (!$lesson) {
            $this->response->withError('Lesson not found.');
            $this->response->redirect('/courses');
            return;
        }

        $course = $this->courseService->getCourseById($lesson['course_id']);
        if (!$course) {
            $this->response->withError('Associated course not found.');
            $this->response->redirect('/courses');
            return;
        }

        if ($course['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only edit lessons in your own courses.');
            $this->response->redirect('/lessons/' . $id);
            return;
        }

        $data = [
            'title'  => 'Edit Lesson',
            'user'   => $user,
            'lesson' => $lesson,
            'course' => $course,
            'error'  => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'lessons/edit', $data);
    }

    public function update(string $id): void
    {
        $user   = $this->auth->user();
        $lesson = $this->lessonService->getLessonById($id);

        if (!$lesson) {
            $this->response->withError('Lesson not found.');
            $this->response->redirect('/courses');
            return;
        }

        $course = $this->courseService->getCourseById($lesson['course_id']);
        if ($course['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only edit lessons in your own courses.');
            $this->response->redirect('/lessons/' . $id);
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'   => 'required|min:3|max:200',
            'content' => 'required|min:10',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/lessons/' . $id . '/edit');
            return;
        }

        $this->lessonService->updateLesson($id, [
            'title'     => $input['title'],
            'content'   => $input['content'],
            'type'      => $input['type'] ?? $lesson['type'],
            'video_url' => $input['video_url'] ?? $lesson['video_url'],
        ]);

        $this->response->withSuccess('Lesson updated successfully!');
        $this->response->redirect('/lessons/' . $id);
    }

    public function delete(string $id): void
    {
        $user   = $this->auth->user();
        $lesson = $this->lessonService->getLessonById($id);

        if (!$lesson) {
            $this->response->withError('Lesson not found.');
            $this->response->redirect('/courses');
            return;
        }

        $courseId = $lesson['course_id'];
        $course   = $this->courseService->getCourseById($courseId);

        if ($course && $course['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only delete lessons in your own courses.');
            $this->response->redirect('/lessons/' . $id);
            return;
        }

        $this->lessonService->deleteLesson($id);

        $this->response->withSuccess('Lesson deleted successfully.');
        $this->response->redirect('/courses/' . $courseId);
    }
}
