<?php

namespace Controllers;

use Core\View;
use Core\Request;
use Core\Response;
use Core\Auth;
use Core\Session;
use Core\JsonStore;
use Services\GradeService;
use Services\CourseService;
use Services\UserService;

class GradeController
{
    private View $view;
    private Request $request;
    private Response $response;
    private Session $session;
    private Auth $auth;
    private GradeService $gradeService;
    private CourseService $courseService;
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

        $this->gradeService  = new GradeService();
        $this->courseService = new CourseService();
        $this->userService   = new UserService();
    }

    public function index(): void
    {
        $user = $this->auth->user();
        if (!$user) {
            $this->response->withError('Please log in to view grades.');
            $this->response->redirect('/login');
            return;
        }

        if ($this->auth->isStudent()) {
            $this->studentGrades($user);
        } elseif ($this->auth->isTeacher()) {
            $this->teacherGrades($user);
        } else {
            $this->adminGrades($user);
        }
    }

    private function studentGrades(array $user): void
    {
        $userId = $user['id'];
        $grades = $this->gradeService->getGradesForStudent($userId);
        $gpa    = $this->gradeService->calculateGPA($userId);

        // Group grades by course
        $enrolledCourses = $this->courseService->getCoursesByStudent($userId);
        $gradesByCourse  = [];

        foreach ($enrolledCourses as $course) {
            $courseGrades = $this->gradeService->getGradesForCourse($course['id'], $userId);
            $avg = 0;
            if (!empty($courseGrades)) {
                $avg = round(array_sum(array_column($courseGrades, 'score')) / count($courseGrades), 1);
            }
            $gradesByCourse[] = [
                'course' => $course,
                'grades' => $courseGrades,
                'average' => $avg,
                'letter'  => $this->gradeService->getLetterGrade($avg),
            ];
        }

        $data = [
            'title'            => 'My Grades',
            'user'             => $user,
            'grades'           => $grades,
            'gpa'              => $gpa,
            'grades_by_course' => $gradesByCourse,
            'success'          => $this->session->getFlash('success'),
            'error'            => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'grades/student', $data);
    }

    private function teacherGrades(array $user): void
    {
        $courses = $this->courseService->getCoursesByTeacher($user['id']);
        $courseData = [];

        foreach ($courses as $course) {
            $avg      = $this->gradeService->getCourseAverage($course['id']);
            $rankings = $this->gradeService->getClassRankings($course['id']);

            // Resolve student names
            foreach ($rankings as &$rank) {
                $student = $this->userService->findById($rank['student_id']);
                $rank['student_name'] = $student['name'] ?? 'Unknown';
            }
            unset($rank);

            $courseData[] = [
                'course'   => $course,
                'average'  => $avg,
                'rankings' => $rankings,
            ];
        }

        $data = [
            'title'       => 'Class Grades',
            'user'        => $user,
            'course_data' => $courseData,
            'success'     => $this->session->getFlash('success'),
            'error'       => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'grades/teacher', $data);
    }

    private function adminGrades(array $user): void
    {
        $courses = $this->courseService->getAllCourses();
        $courseData = [];

        foreach ($courses as $course) {
            $courseData[] = [
                'course'  => $course,
                'average' => $this->gradeService->getCourseAverage($course['id']),
            ];
        }

        $data = [
            'title'       => 'All Grades',
            'user'        => $user,
            'course_data' => $courseData,
            'success'     => $this->session->getFlash('success'),
            'error'       => $this->session->getFlash('error'),
        ];

        echo $this->view->layout('main', 'grades/admin', $data);
    }
}
