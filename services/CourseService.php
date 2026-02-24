<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for course management and enrollment.
 */
class CourseService
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore('courses.json');
    }

    /**
     * Return every course.
     */
    public function getAllCourses(): array
    {
        return $this->store->readAll();
    }

    /**
     * Get a single course by ID.
     */
    public function getCourseById(string $id): ?array
    {
        return $this->store->find($id);
    }

    /**
     * Courses created by a specific teacher.
     */
    public function getCoursesByTeacher(string $teacherId): array
    {
        return $this->store->findBy('teacher_id', $teacherId);
    }

    /**
     * Courses in which a student is enrolled.
     */
    public function getCoursesByStudent(string $studentId): array
    {
        return array_values(array_filter(
            $this->store->readAll(),
            function (array $course) use ($studentId) {
                $enrolled = $course['enrolled_students'] ?? [];
                return in_array($studentId, $enrolled, true);
            }
        ));
    }

    /**
     * Create a new course.
     *
     * @param array $data Must include 'title', 'teacher_id'. Optional: 'description', 'subject', 'status'.
     */
    public function createCourse(array $data): array
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new \InvalidArgumentException('Course title is required.');
        }

        $record = [
            'title'             => trim($data['title']),
            'description'       => trim($data['description'] ?? ''),
            'subject'           => $data['subject'] ?? '',
            'teacher_id'        => $data['teacher_id'] ?? '',
            'status'            => $data['status'] ?? 'active',
            'enrolled_students' => [],
            'image'             => $data['image'] ?? null,
        ];

        return $this->store->create($record);
    }

    /**
     * Update course fields.
     */
    public function updateCourse(string $id, array $data): ?array
    {
        if (!$this->store->find($id)) {
            return null;
        }

        return $this->store->update($id, $data);
    }

    /**
     * Delete a course and its associated lessons.
     */
    public function deleteCourse(string $id): bool
    {
        if (!$this->store->delete($id)) {
            return false;
        }

        // Cascade-delete lessons belonging to this course
        $lessons = new JsonStore('lessons.json');
        foreach ($lessons->findBy('course_id', $id) as $lesson) {
            $lessons->delete($lesson['id']);
        }

        return true;
    }

    /**
     * Enroll a student in a course (idempotent).
     */
    public function enrollStudent(string $courseId, string $studentId): ?array
    {
        $course = $this->store->find($courseId);
        if (!$course) {
            return null;
        }

        $enrolled = $course['enrolled_students'] ?? [];
        if (!in_array($studentId, $enrolled, true)) {
            $enrolled[] = $studentId;
        }

        return $this->store->update($courseId, ['enrolled_students' => $enrolled]);
    }

    /**
     * Remove a student from a course.
     */
    public function unenrollStudent(string $courseId, string $studentId): ?array
    {
        $course = $this->store->find($courseId);
        if (!$course) {
            return null;
        }

        $enrolled = array_values(array_filter(
            $course['enrolled_students'] ?? [],
            fn(string $id) => $id !== $studentId
        ));

        return $this->store->update($courseId, ['enrolled_students' => $enrolled]);
    }

    /**
     * Get the list of enrolled student IDs.
     */
    public function getEnrollments(string $courseId): array
    {
        $course = $this->store->find($courseId);
        return $course['enrolled_students'] ?? [];
    }

    /**
     * Check whether a student is enrolled.
     */
    public function isEnrolled(string $courseId, string $studentId): bool
    {
        return in_array($studentId, $this->getEnrollments($courseId), true);
    }

    /**
     * Full-text search on title and description.
     */
    public function searchCourses(string $query): array
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return [];
        }

        return array_values(array_filter(
            $this->store->readAll(),
            function (array $course) use ($query) {
                return str_contains(mb_strtolower($course['title'] ?? ''), $query)
                    || str_contains(mb_strtolower($course['description'] ?? ''), $query)
                    || str_contains(mb_strtolower($course['subject'] ?? ''), $query);
            }
        ));
    }

    /**
     * Aggregate stats for a course.
     */
    public function getCourseStats(string $courseId): array
    {
        $course = $this->store->find($courseId);
        if (!$course) {
            return [];
        }

        $lessons     = new JsonStore('lessons.json');
        $assignments = new JsonStore('assignments.json');
        $quizzes     = new JsonStore('quizzes.json');

        return [
            'student_count'    => count($course['enrolled_students'] ?? []),
            'lesson_count'     => count($lessons->findBy('course_id', $courseId)),
            'assignment_count' => count($assignments->findBy('course_id', $courseId)),
            'quiz_count'       => count($quizzes->findBy('course_id', $courseId)),
            'status'           => $course['status'] ?? 'active',
        ];
    }
}
