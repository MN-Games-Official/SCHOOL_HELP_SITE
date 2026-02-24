<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\QuizService;
use Services\CourseService;

class QuizController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private QuizService $quizService;
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

        $this->quizService   = new QuizService();
        $this->courseService  = new CourseService();
    }

    public function index(): void
    {
        $user    = $this->auth->user();
        $quizzes = $this->quizService->getAllQuizzes();

        // For students, show only quizzes from enrolled courses
        if ($this->auth->isStudent()) {
            $enrolledCourses = $this->courseService->getCoursesByStudent($user['id']);
            $enrolledIds     = array_column($enrolledCourses, 'id');
            $quizzes = array_values(array_filter(
                $quizzes,
                fn(array $q) => in_array($q['course_id'] ?? '', $enrolledIds, true)
            ));
        }

        // For teachers, show only their quizzes
        if ($this->auth->isTeacher()) {
            $quizzes = $this->quizService->getQuizzesByTeacher($user['id']);
        }

        $data = [
            'title'   => 'Quizzes',
            'user'    => $user,
            'quizzes' => $quizzes,
            'success' => $this->session->getFlash('success'),
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'quizzes/index', $data);
    }

    public function show(string $id): void
    {
        $user = $this->auth->user();
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            $this->response->withError('Quiz not found.');
            $this->response->redirect('/quizzes');
            return;
        }

        $course   = $this->courseService->getCourseById($quiz['course_id'] ?? '');
        $attempts = $user ? $this->quizService->getAttempts($id, $user['id']) : [];

        $data = [
            'title'         => $quiz['title'],
            'user'          => $user,
            'quiz'          => $quiz,
            'course'        => $course,
            'attempts'      => $attempts,
            'attempt_count' => count($attempts),
            'max_attempts'  => $quiz['max_attempts'] ?? 1,
            'success'       => $this->session->getFlash('success'),
            'error'         => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'quizzes/show', $data);
    }

    public function create(): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can create quizzes.');
            $this->response->redirect('/quizzes');
            return;
        }

        $user    = $this->auth->user();
        $courses = $this->courseService->getCoursesByTeacher($user['id']);

        $data = [
            'title'   => 'Create Quiz',
            'user'    => $user,
            'courses' => $courses,
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'quizzes/create', $data);
    }

    public function store(): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can create quizzes.');
            $this->response->redirect('/quizzes');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'     => 'required|min:3|max:200',
            'course_id' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/quizzes/create');
            return;
        }

        // Parse questions from form input
        $questions = [];
        if (isset($input['questions']) && is_array($input['questions'])) {
            foreach ($input['questions'] as $q) {
                if (empty(trim($q['question'] ?? ''))) {
                    continue;
                }
                $questions[] = [
                    'question'       => trim($q['question']),
                    'options'        => $q['options'] ?? [],
                    'correct_answer' => (int) ($q['correct_answer'] ?? 0),
                ];
            }
        }

        if (empty($questions)) {
            $this->response->withError('At least one question is required.');
            $this->response->redirect('/quizzes/create');
            return;
        }

        $user = $this->auth->user();

        $quiz = $this->quizService->createQuiz([
            'title'        => $input['title'],
            'description'  => $input['description'] ?? '',
            'course_id'    => $input['course_id'],
            'teacher_id'   => $user['id'],
            'questions'    => $questions,
            'time_limit'   => !empty($input['time_limit']) ? (int) $input['time_limit'] : null,
            'max_attempts' => !empty($input['max_attempts']) ? (int) $input['max_attempts'] : 1,
            'due_date'     => $input['due_date'] ?? null,
        ]);

        $this->response->withSuccess('Quiz created successfully!');
        $this->response->redirect('/quizzes/' . $quiz['id']);
    }

    public function take(string $id): void
    {
        $user = $this->auth->user();
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            $this->response->withError('Quiz not found.');
            $this->response->redirect('/quizzes');
            return;
        }

        // Check max attempts
        $attempts    = $this->quizService->getAttempts($id, $user['id']);
        $maxAttempts = $quiz['max_attempts'] ?? 1;
        if ($maxAttempts > 0 && count($attempts) >= $maxAttempts) {
            $this->response->withError('You have reached the maximum number of attempts for this quiz.');
            $this->response->redirect('/quizzes/' . $id);
            return;
        }

        $data = [
            'title'          => 'Take Quiz: ' . $quiz['title'],
            'user'           => $user,
            'quiz'           => $quiz,
            'questions'      => $quiz['questions'] ?? [],
            'attempt_number' => count($attempts) + 1,
            'error'          => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'quizzes/take', $data);
    }

    public function submit(string $id): void
    {
        $user = $this->auth->user();
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            $this->response->withError('Quiz not found.');
            $this->response->redirect('/quizzes');
            return;
        }

        $input   = $this->request->all();
        $answers = $input['answers'] ?? [];

        try {
            $attempt = $this->quizService->submitAttempt($id, $user['id'], $answers);
        } catch (\RuntimeException $e) {
            $this->response->withError($e->getMessage());
            $this->response->redirect('/quizzes/' . $id);
            return;
        }

        $this->response->withSuccess(
            'Quiz submitted! You scored ' . $attempt['score'] . '/' . $attempt['total']
            . ' (' . $attempt['percentage'] . '%)'
        );
        $this->response->redirect('/quizzes/' . $id . '/results');
    }

    public function results(string $id): void
    {
        $user = $this->auth->user();
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            $this->response->withError('Quiz not found.');
            $this->response->redirect('/quizzes');
            return;
        }

        $attempts = $this->quizService->getAttempts($id, $user['id']);

        // Teachers can see all results
        $allResults = [];
        if ($this->auth->isTeacher() || $this->auth->isAdmin()) {
            $allResults = $this->quizService->getQuizResults($id);
        }

        $data = [
            'title'       => 'Quiz Results: ' . $quiz['title'],
            'user'        => $user,
            'quiz'        => $quiz,
            'attempts'    => $attempts,
            'all_results' => $allResults,
            'success'     => $this->session->getFlash('success'),
        ];

        echo $this->view->layout('main', 'quizzes/results', $data);
    }

    public function edit(string $id): void
    {
        $user = $this->auth->user();
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            $this->response->withError('Quiz not found.');
            $this->response->redirect('/quizzes');
            return;
        }

        if ($quiz['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only edit your own quizzes.');
            $this->response->redirect('/quizzes/' . $id);
            return;
        }

        $courses = $this->courseService->getCoursesByTeacher($user['id']);

        $data = [
            'title'   => 'Edit Quiz',
            'user'    => $user,
            'quiz'    => $quiz,
            'courses' => $courses,
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'quizzes/edit', $data);
    }

    public function update(string $id): void
    {
        $user = $this->auth->user();
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            $this->response->withError('Quiz not found.');
            $this->response->redirect('/quizzes');
            return;
        }

        if ($quiz['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only edit your own quizzes.');
            $this->response->redirect('/quizzes/' . $id);
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'     => 'required|min:3|max:200',
            'course_id' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/quizzes/' . $id . '/edit');
            return;
        }

        $updateData = [
            'title'        => $input['title'],
            'description'  => $input['description'] ?? '',
            'course_id'    => $input['course_id'],
            'time_limit'   => !empty($input['time_limit']) ? (int) $input['time_limit'] : null,
            'max_attempts' => !empty($input['max_attempts']) ? (int) $input['max_attempts'] : 1,
            'due_date'     => $input['due_date'] ?? null,
        ];

        // Update questions if provided
        if (isset($input['questions']) && is_array($input['questions'])) {
            $questions = [];
            foreach ($input['questions'] as $q) {
                if (empty(trim($q['question'] ?? ''))) {
                    continue;
                }
                $questions[] = [
                    'question'       => trim($q['question']),
                    'options'        => $q['options'] ?? [],
                    'correct_answer' => (int) ($q['correct_answer'] ?? 0),
                ];
            }
            if (!empty($questions)) {
                $updateData['questions'] = $questions;
            }
        }

        $this->quizService->updateQuiz($id, $updateData);

        $this->response->withSuccess('Quiz updated successfully!');
        $this->response->redirect('/quizzes/' . $id);
    }

    public function delete(string $id): void
    {
        $user = $this->auth->user();
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            $this->response->withError('Quiz not found.');
            $this->response->redirect('/quizzes');
            return;
        }

        if ($quiz['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only delete your own quizzes.');
            $this->response->redirect('/quizzes/' . $id);
            return;
        }

        $this->quizService->deleteQuiz($id);

        $this->response->withSuccess('Quiz deleted successfully.');
        $this->response->redirect('/quizzes');
    }
}
