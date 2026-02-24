<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\Validator;
use Core\JsonStore;
use Services\AssignmentService;
use Services\CourseService;
use Services\NotificationService;

class AssignmentController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private AssignmentService $assignmentService;
    private CourseService $courseService;
    private NotificationService $notificationService;

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

        $this->assignmentService   = new AssignmentService();
        $this->courseService       = new CourseService();
        $this->notificationService = new NotificationService();
    }

    public function index(): void
    {
        $user = $this->auth->user();

        if ($this->auth->isTeacher()) {
            $assignments = $this->assignmentService->getAssignmentsByTeacher($user['id']);
        } elseif ($this->auth->isAdmin()) {
            $assignments = $this->assignmentService->getAllAssignments();
        } else {
            $assignments = $this->assignmentService->getAssignmentsForStudent($user['id']);
        }

        $data = [
            'title'       => 'Assignments',
            'user'        => $user,
            'assignments' => $assignments,
            'success'     => $this->session->getFlash('success'),
            'error'       => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'assignments/index', $data);
    }

    public function show(string $id): void
    {
        $user       = $this->auth->user();
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            $this->response->withError('Assignment not found.');
            $this->response->redirect('/assignments');
            return;
        }

        $course     = $this->courseService->getCourseById($assignment['course_id'] ?? '');
        $submission = null;
        $submissions = [];

        if ($this->auth->isStudent()) {
            $submission = $this->assignmentService->getSubmission($id, $user['id']);
        }

        if ($this->auth->isTeacher() || $this->auth->isAdmin()) {
            $submissions = $this->assignmentService->getSubmissions($id);
        }

        $isOverdue = $this->assignmentService->isOverdue($assignment);

        $data = [
            'title'       => $assignment['title'],
            'user'        => $user,
            'assignment'  => $assignment,
            'course'      => $course,
            'submission'  => $submission,
            'submissions' => $submissions,
            'is_overdue'  => $isOverdue,
            'success'     => $this->session->getFlash('success'),
            'error'       => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'assignments/show', $data);
    }

    public function create(): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can create assignments.');
            $this->response->redirect('/assignments');
            return;
        }

        $user    = $this->auth->user();
        $courses = $this->courseService->getCoursesByTeacher($user['id']);

        $data = [
            'title'   => 'Create Assignment',
            'user'    => $user,
            'courses' => $courses,
            'error'   => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'assignments/create', $data);
    }

    public function store(): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can create assignments.');
            $this->response->redirect('/assignments');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'       => 'required|min:3|max:200',
            'description' => 'required|min:10',
            'course_id'   => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/assignments/create');
            return;
        }

        $user = $this->auth->user();

        $assignment = $this->assignmentService->createAssignment([
            'title'       => $input['title'],
            'description' => $input['description'],
            'course_id'   => $input['course_id'],
            'teacher_id'  => $user['id'],
            'due_date'    => $input['due_date'] ?? null,
            'max_score'   => !empty($input['max_score']) ? (int) $input['max_score'] : 100,
        ]);

        $this->response->withSuccess('Assignment created successfully!');
        $this->response->redirect('/assignments/' . $assignment['id']);
    }

    public function submit(string $id): void
    {
        $user       = $this->auth->user();
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            $this->response->withError('Assignment not found.');
            $this->response->redirect('/assignments');
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'content' => 'required|min:10',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Please provide your submission content.');
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        try {
            $this->assignmentService->submitWork($id, $user['id'], [
                'content' => $input['content'],
            ]);
        } catch (\RuntimeException $e) {
            $this->response->withError($e->getMessage());
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        // Notify the teacher
        $this->notificationService->create(
            $assignment['teacher_id'],
            'assignment',
            $user['name'] . ' submitted work for "' . $assignment['title'] . '"',
            '/assignments/' . $id
        );

        $this->response->withSuccess('Assignment submitted successfully!');
        $this->response->redirect('/assignments/' . $id);
    }

    public function edit(string $id): void
    {
        $user       = $this->auth->user();
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            $this->response->withError('Assignment not found.');
            $this->response->redirect('/assignments');
            return;
        }

        if ($assignment['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only edit your own assignments.');
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        $courses = $this->courseService->getCoursesByTeacher($user['id']);

        $data = [
            'title'      => 'Edit Assignment',
            'user'       => $user,
            'assignment' => $assignment,
            'courses'    => $courses,
            'error'      => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'assignments/edit', $data);
    }

    public function update(string $id): void
    {
        $user       = $this->auth->user();
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            $this->response->withError('Assignment not found.');
            $this->response->redirect('/assignments');
            return;
        }

        if ($assignment['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only edit your own assignments.');
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'title'       => 'required|min:3|max:200',
            'description' => 'required|min:10',
            'course_id'   => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Validation failed.');
            $this->response->redirect('/assignments/' . $id . '/edit');
            return;
        }

        $this->assignmentService->updateAssignment($id, [
            'title'       => $input['title'],
            'description' => $input['description'],
            'course_id'   => $input['course_id'],
            'due_date'    => $input['due_date'] ?? $assignment['due_date'],
            'max_score'   => !empty($input['max_score']) ? (int) $input['max_score'] : ($assignment['max_score'] ?? 100),
        ]);

        $this->response->withSuccess('Assignment updated successfully!');
        $this->response->redirect('/assignments/' . $id);
    }

    public function delete(string $id): void
    {
        $user       = $this->auth->user();
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            $this->response->withError('Assignment not found.');
            $this->response->redirect('/assignments');
            return;
        }

        if ($assignment['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only delete your own assignments.');
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        $this->assignmentService->deleteAssignment($id);

        $this->response->withSuccess('Assignment deleted successfully.');
        $this->response->redirect('/assignments');
    }

    public function grade(string $id): void
    {
        if (!$this->auth->isTeacher() && !$this->auth->isAdmin()) {
            $this->response->withError('Only teachers can grade assignments.');
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        $user       = $this->auth->user();
        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            $this->response->withError('Assignment not found.');
            $this->response->redirect('/assignments');
            return;
        }

        if ($assignment['teacher_id'] !== $user['id'] && !$this->auth->isAdmin()) {
            $this->response->withError('You can only grade submissions for your own assignments.');
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        $validator = new Validator();
        $input = $this->request->all();

        $validator->validate($input, [
            'submission_id' => 'required',
            'grade'         => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = reset($errors);
            $this->response->withError($firstError[0] ?? 'Please provide a grade.');
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        $grade    = (float) $input['grade'];
        $maxScore = $assignment['max_score'] ?? 100;

        if ($grade < 0 || $grade > $maxScore) {
            $this->response->withError("Grade must be between 0 and {$maxScore}.");
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        $result = $this->assignmentService->gradeSubmission(
            $input['submission_id'],
            $grade,
            $input['feedback'] ?? ''
        );

        if (!$result) {
            $this->response->withError('Submission not found.');
            $this->response->redirect('/assignments/' . $id);
            return;
        }

        // Notify the student
        $this->notificationService->create(
            $result['student_id'],
            'grade',
            'Your submission for "' . $assignment['title'] . '" has been graded.',
            '/assignments/' . $id
        );

        $this->response->withSuccess('Submission graded successfully!');
        $this->response->redirect('/assignments/' . $id);
    }
}
