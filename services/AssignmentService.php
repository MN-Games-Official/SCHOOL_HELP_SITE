<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for assignment and submission management.
 */
class AssignmentService
{
    private JsonStore $assignmentStore;
    private JsonStore $submissionStore;

    public function __construct()
    {
        $this->assignmentStore = new JsonStore('assignments.json');
        $this->submissionStore = new JsonStore('submissions.json');
    }

    /**
     * Return every assignment.
     */
    public function getAllAssignments(): array
    {
        return $this->assignmentStore->readAll();
    }

    /**
     * Get a single assignment by ID.
     */
    public function getAssignmentById(string $id): ?array
    {
        return $this->assignmentStore->find($id);
    }

    /**
     * Assignments for a specific course.
     */
    public function getAssignmentsByCourse(string $courseId): array
    {
        return $this->assignmentStore->findBy('course_id', $courseId);
    }

    /**
     * Assignments created by a teacher.
     */
    public function getAssignmentsByTeacher(string $teacherId): array
    {
        return $this->assignmentStore->findBy('teacher_id', $teacherId);
    }

    /**
     * Get assignments from every course the student is enrolled in.
     */
    public function getAssignmentsForStudent(string $studentId): array
    {
        $courseStore = new JsonStore('courses.json');
        $enrolledCourseIds = [];

        foreach ($courseStore->readAll() as $course) {
            $enrolled = $course['enrolled_students'] ?? [];
            if (in_array($studentId, $enrolled, true)) {
                $enrolledCourseIds[] = $course['id'];
            }
        }

        if (empty($enrolledCourseIds)) {
            return [];
        }

        return array_values(array_filter(
            $this->assignmentStore->readAll(),
            fn(array $a) => in_array($a['course_id'] ?? '', $enrolledCourseIds, true)
        ));
    }

    /**
     * Create a new assignment.
     *
     * @param array $data Must include 'title', 'course_id', 'teacher_id'.
     */
    public function createAssignment(array $data): array
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new \InvalidArgumentException('Assignment title is required.');
        }

        $record = [
            'title'       => trim($data['title']),
            'description' => trim($data['description'] ?? ''),
            'course_id'   => $data['course_id'] ?? '',
            'teacher_id'  => $data['teacher_id'] ?? '',
            'due_date'    => $data['due_date'] ?? null,
            'max_score'   => (int) ($data['max_score'] ?? 100),
            'status'      => $data['status'] ?? 'active',
            'attachments' => $data['attachments'] ?? [],
        ];

        return $this->assignmentStore->create($record);
    }

    /**
     * Update assignment fields.
     */
    public function updateAssignment(string $id, array $data): ?array
    {
        if (!$this->assignmentStore->find($id)) {
            return null;
        }

        return $this->assignmentStore->update($id, $data);
    }

    /**
     * Delete an assignment and its submissions.
     */
    public function deleteAssignment(string $id): bool
    {
        if (!$this->assignmentStore->delete($id)) {
            return false;
        }

        foreach ($this->submissionStore->findBy('assignment_id', $id) as $sub) {
            $this->submissionStore->delete($sub['id']);
        }

        return true;
    }

    /**
     * Submit work for an assignment.
     *
     * @param string $assignmentId
     * @param string $studentId
     * @param array  $data May include 'content', 'file_path'.
     * @return array The created submission.
     */
    public function submitWork(string $assignmentId, string $studentId, array $data): array
    {
        $assignment = $this->assignmentStore->find($assignmentId);
        if (!$assignment) {
            throw new \RuntimeException('Assignment not found.');
        }

        // Check for existing submission and update if present
        $existing = $this->getSubmission($assignmentId, $studentId);
        if ($existing) {
            return $this->submissionStore->update($existing['id'], [
                'content'   => $data['content'] ?? $existing['content'],
                'file_path' => $data['file_path'] ?? $existing['file_path'],
                'status'    => 'resubmitted',
            ]);
        }

        $record = [
            'assignment_id' => $assignmentId,
            'student_id'    => $studentId,
            'content'       => $data['content'] ?? '',
            'file_path'     => $data['file_path'] ?? null,
            'status'        => 'submitted',
            'grade'         => null,
            'feedback'      => null,
        ];

        return $this->submissionStore->create($record);
    }

    /**
     * Get a specific student's submission for an assignment.
     */
    public function getSubmission(string $assignmentId, string $studentId): ?array
    {
        $matches = array_filter(
            $this->submissionStore->readAll(),
            fn(array $s) => ($s['assignment_id'] ?? '') === $assignmentId
                         && ($s['student_id'] ?? '') === $studentId
        );

        return !empty($matches) ? array_values($matches)[0] : null;
    }

    /**
     * Get all submissions for an assignment.
     */
    public function getSubmissions(string $assignmentId): array
    {
        return $this->submissionStore->findBy('assignment_id', $assignmentId);
    }

    /**
     * Grade a submission.
     */
    public function gradeSubmission(string $submissionId, float $grade, string $feedback = ''): ?array
    {
        $submission = $this->submissionStore->find($submissionId);
        if (!$submission) {
            return null;
        }

        return $this->submissionStore->update($submissionId, [
            'grade'     => $grade,
            'feedback'  => $feedback,
            'status'    => 'graded',
            'graded_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ]);
    }

    /**
     * Check whether an assignment is past its due date.
     */
    public function isOverdue(array $assignment): bool
    {
        $dueDate = $assignment['due_date'] ?? null;
        if ($dueDate === null) {
            return false;
        }

        return strtotime($dueDate) < time();
    }
}
