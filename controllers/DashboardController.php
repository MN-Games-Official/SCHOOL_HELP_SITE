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
use Services\AssignmentService;
use Services\QuizService;
use Services\GradeService;
use Services\NotificationService;
use Services\UserService;

class DashboardController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private CourseService $courseService;
    private AssignmentService $assignmentService;
    private QuizService $quizService;
    private GradeService $gradeService;
    private NotificationService $notificationService;
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

        $this->courseService       = new CourseService();
        $this->assignmentService   = new AssignmentService();
        $this->quizService         = new QuizService();
        $this->gradeService        = new GradeService();
        $this->notificationService = new NotificationService();
        $this->userService         = new UserService();
    }

    public function index(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->withError('Please log in to continue.');
            $this->response->redirect('/login');
            return;
        }

        $userId = $user['id'];
        $role   = $user['role'] ?? 'student';

        $notifications = $this->notificationService->getUnread($userId);

        if ($role === 'admin') {
            $this->adminDashboard($user, $notifications);
        } elseif ($role === 'teacher') {
            $this->teacherDashboard($user, $notifications);
        } else {
            $this->studentDashboard($user, $notifications);
        }
    }

    private function studentDashboard(array $user, array $notifications): void
    {
        $userId = $user['id'];

        $enrolledCourses = $this->courseService->getCoursesByStudent($userId);
        $assignments     = $this->assignmentService->getAssignmentsForStudent($userId);
        $grades          = $this->gradeService->getGradesForStudent($userId);
        $gpa             = $this->gradeService->calculateGPA($userId);

        // Upcoming assignments (not yet past due)
        $now = gmdate('Y-m-d\TH:i:s\Z');
        $upcomingAssignments = array_filter($assignments, function (array $a) use ($now) {
            $due = $a['due_date'] ?? null;
            return $due === null || $due >= $now;
        });
        $upcomingAssignments = array_slice(array_values($upcomingAssignments), 0, 5);

        // Recent grades
        usort($grades, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $recentGrades = array_slice($grades, 0, 5);

        $data = [
            'title'                => 'Student Dashboard',
            'user'                 => $user,
            'role'                 => 'student',
            'enrolled_courses'     => $enrolledCourses,
            'enrolled_count'       => count($enrolledCourses),
            'upcoming_assignments' => $upcomingAssignments,
            'recent_grades'        => $recentGrades,
            'gpa'                  => $gpa,
            'notifications'        => $notifications,
            'success'              => $this->session->getFlash('success'),
            'error'                => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'dashboard/student', $data);
    }

    private function teacherDashboard(array $user, array $notifications): void
    {
        $userId = $user['id'];

        $courses       = $this->courseService->getCoursesByTeacher($userId);
        $assignments   = $this->assignmentService->getAssignmentsByTeacher($userId);

        // Count total enrolled students across all courses
        $totalStudents = 0;
        foreach ($courses as $course) {
            $totalStudents += count($course['enrolled_students'] ?? []);
        }

        // Pending submissions (ungraded)
        $submissionStore   = new JsonStore('submissions.json');
        $allSubmissions    = $submissionStore->readAll();
        $teacherAssignIds  = array_column($assignments, 'id');
        $pendingSubmissions = array_filter($allSubmissions, function (array $s) use ($teacherAssignIds) {
            return in_array($s['assignment_id'] ?? '', $teacherAssignIds, true)
                && $s['grade'] === null;
        });

        $data = [
            'title'               => 'Teacher Dashboard',
            'user'                => $user,
            'role'                => 'teacher',
            'courses'             => $courses,
            'course_count'        => count($courses),
            'student_count'       => $totalStudents,
            'assignments'         => $assignments,
            'pending_submissions' => count($pendingSubmissions),
            'notifications'       => $notifications,
            'success'             => $this->session->getFlash('success'),
            'error'               => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'dashboard/teacher', $data);
    }

    private function adminDashboard(array $user, array $notifications): void
    {
        $allUsers   = $this->userService->getAllUsers();
        $allCourses = $this->courseService->getAllCourses();

        $students = array_filter($allUsers, fn($u) => ($u['role'] ?? '') === 'student');
        $teachers = array_filter($allUsers, fn($u) => ($u['role'] ?? '') === 'teacher');

        $data = [
            'title'          => 'Admin Dashboard',
            'user'           => $user,
            'role'            => 'admin',
            'total_users'    => count($allUsers),
            'total_students' => count($students),
            'total_teachers' => count($teachers),
            'total_courses'  => count($allCourses),
            'notifications'  => $notifications,
            'recent_users'   => array_slice(array_reverse($allUsers), 0, 10),
            'success'        => $this->session->getFlash('success'),
            'error'          => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'dashboard/admin', $data);
    }
}
