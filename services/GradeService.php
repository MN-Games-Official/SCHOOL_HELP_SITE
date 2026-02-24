<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for grade aggregation, GPA calculation and rankings.
 */
class GradeService
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore('grades.json');
    }

    /**
     * Aggregate all grades for a student (quiz scores + assignment grades).
     */
    public function getGradesForStudent(string $studentId): array
    {
        $grades = $this->store->findBy('student_id', $studentId);

        // Merge in quiz attempt scores
        $attempts = (new JsonStore('quiz_attempts.json'))->findBy('user_id', $studentId);
        foreach ($attempts as $attempt) {
            $grades[] = [
                'id'         => $attempt['id'],
                'student_id' => $studentId,
                'type'       => 'quiz',
                'source_id'  => $attempt['quiz_id'] ?? '',
                'score'      => $attempt['percentage'] ?? 0,
                'created_at' => $attempt['created_at'] ?? '',
            ];
        }

        // Merge in graded assignment submissions
        $submissions = (new JsonStore('submissions.json'))->findBy('student_id', $studentId);
        foreach ($submissions as $sub) {
            if ($sub['grade'] === null) {
                continue;
            }
            $assignment = (new JsonStore('assignments.json'))->find($sub['assignment_id'] ?? '');
            $maxScore   = $assignment['max_score'] ?? 100;
            $pct        = $maxScore > 0 ? round(($sub['grade'] / $maxScore) * 100, 1) : 0;

            $grades[] = [
                'id'         => $sub['id'],
                'student_id' => $studentId,
                'type'       => 'assignment',
                'source_id'  => $sub['assignment_id'] ?? '',
                'score'      => $pct,
                'created_at' => $sub['graded_at'] ?? $sub['created_at'] ?? '',
            ];
        }

        return $grades;
    }

    /**
     * Get grades for a student within a specific course.
     */
    public function getGradesForCourse(string $courseId, string $studentId): array
    {
        $allGrades = $this->getGradesForStudent($studentId);

        $quizzes     = (new JsonStore('quizzes.json'))->findBy('course_id', $courseId);
        $assignments = (new JsonStore('assignments.json'))->findBy('course_id', $courseId);

        $courseSourceIds = array_merge(
            array_column($quizzes, 'id'),
            array_column($assignments, 'id')
        );

        return array_values(array_filter(
            $allGrades,
            fn(array $g) => in_array($g['source_id'] ?? '', $courseSourceIds, true)
        ));
    }

    /**
     * Manually add a grade record.
     */
    public function addGrade(array $data): array
    {
        $record = [
            'student_id' => $data['student_id'] ?? '',
            'course_id'  => $data['course_id'] ?? '',
            'type'       => $data['type'] ?? 'manual',
            'source_id'  => $data['source_id'] ?? '',
            'score'      => (float) ($data['score'] ?? 0),
            'max_score'  => (float) ($data['max_score'] ?? 100),
            'feedback'   => $data['feedback'] ?? '',
        ];

        return $this->store->create($record);
    }

    /**
     * Update a grade record.
     */
    public function updateGrade(string $id, array $data): ?array
    {
        if (!$this->store->find($id)) {
            return null;
        }

        return $this->store->update($id, $data);
    }

    /**
     * Calculate a student's GPA on a 4.0 scale using all grades.
     */
    public function calculateGPA(string $studentId): float
    {
        $grades = $this->getGradesForStudent($studentId);
        if (empty($grades)) {
            return 0.0;
        }

        $gpaMap = [
            'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
            'F'  => 0.0,
        ];

        $total = 0.0;
        $count = 0;
        foreach ($grades as $grade) {
            $letter = $this->getLetterGrade((float) ($grade['score'] ?? 0));
            $total += $gpaMap[$letter] ?? 0.0;
            $count++;
        }

        return $count > 0 ? round($total / $count, 2) : 0.0;
    }

    /**
     * Convert a percentage to a letter grade using the app's grade scale.
     */
    public function getLetterGrade(float $percentage): string
    {
        $configPath = __DIR__ . '/../config/app.php';
        $config     = file_exists($configPath) ? require $configPath : [];
        $scale      = $config['grade_scale'] ?? [];

        $pct = (int) round($percentage);

        foreach ($scale as $letter => $range) {
            if ($pct >= $range[0] && $pct <= $range[1]) {
                return $letter;
            }
        }

        return 'F';
    }

    /**
     * Average score across all students in a course.
     */
    public function getCourseAverage(string $courseId): float
    {
        $courseStore = new JsonStore('courses.json');
        $course     = $courseStore->find($courseId);
        if (!$course) {
            return 0.0;
        }

        $studentIds = $course['enrolled_students'] ?? [];
        if (empty($studentIds)) {
            return 0.0;
        }

        $totalAvg = 0.0;
        $counted  = 0;

        foreach ($studentIds as $sid) {
            $grades = $this->getGradesForCourse($courseId, $sid);
            if (empty($grades)) {
                continue;
            }
            $avg = array_sum(array_column($grades, 'score')) / count($grades);
            $totalAvg += $avg;
            $counted++;
        }

        return $counted > 0 ? round($totalAvg / $counted, 1) : 0.0;
    }

    /**
     * Rank students in a course by average score (descending).
     *
     * @return array List of ['student_id', 'average', 'rank']
     */
    public function getClassRankings(string $courseId): array
    {
        $courseStore = new JsonStore('courses.json');
        $course     = $courseStore->find($courseId);
        if (!$course) {
            return [];
        }

        $rankings = [];

        foreach ($course['enrolled_students'] ?? [] as $sid) {
            $grades = $this->getGradesForCourse($courseId, $sid);
            $avg    = !empty($grades)
                ? array_sum(array_column($grades, 'score')) / count($grades)
                : 0.0;

            $rankings[] = [
                'student_id' => $sid,
                'average'    => round($avg, 1),
            ];
        }

        usort($rankings, fn($a, $b) => $b['average'] <=> $a['average']);

        $rank = 1;
        foreach ($rankings as &$r) {
            $r['rank'] = $rank++;
        }
        unset($r);

        return $rankings;
    }
}
