<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for lesson management and student progress tracking.
 */
class LessonService
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore('lessons.json');
    }

    /**
     * Get all lessons for a course, ordered by sort_order.
     */
    public function getLessonsByCourse(string $courseId): array
    {
        $lessons = $this->store->findBy('course_id', $courseId);

        usort($lessons, function (array $a, array $b) {
            return ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0);
        });

        return $lessons;
    }

    /**
     * Get a single lesson by ID.
     */
    public function getLessonById(string $id): ?array
    {
        return $this->store->find($id);
    }

    /**
     * Create a new lesson within a course.
     *
     * @param array $data Must include 'course_id', 'title'. Optional: 'content', 'sort_order', 'type'.
     */
    public function createLesson(array $data): array
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new \InvalidArgumentException('Lesson title is required.');
        }
        if (empty($data['course_id'] ?? '')) {
            throw new \InvalidArgumentException('Course ID is required.');
        }

        // Auto-assign sort_order if not provided
        if (!isset($data['sort_order'])) {
            $existing = $this->getLessonsByCourse($data['course_id']);
            $data['sort_order'] = count($existing) + 1;
        }

        $record = [
            'course_id'      => $data['course_id'],
            'title'          => trim($data['title']),
            'content'        => $data['content'] ?? '',
            'type'           => $data['type'] ?? 'text',
            'sort_order'     => (int) $data['sort_order'],
            'video_url'      => $data['video_url'] ?? null,
            'attachments'    => $data['attachments'] ?? [],
            'completed_by'   => [],
        ];

        return $this->store->create($record);
    }

    /**
     * Update lesson fields.
     */
    public function updateLesson(string $id, array $data): ?array
    {
        if (!$this->store->find($id)) {
            return null;
        }

        return $this->store->update($id, $data);
    }

    /**
     * Delete a lesson.
     */
    public function deleteLesson(string $id): bool
    {
        return $this->store->delete($id);
    }

    /**
     * Reorder lessons within a course.
     *
     * @param string $courseId
     * @param array  $order Ordered array of lesson IDs.
     */
    public function reorderLessons(string $courseId, array $order): void
    {
        $lessons = $this->getLessonsByCourse($courseId);
        $lessonMap = [];
        foreach ($lessons as $lesson) {
            $lessonMap[$lesson['id']] = $lesson;
        }

        $position = 1;
        foreach ($order as $lessonId) {
            if (isset($lessonMap[$lessonId])) {
                $this->store->update($lessonId, ['sort_order' => $position]);
                $position++;
            }
        }
    }

    /**
     * Mark a lesson as completed by a user (idempotent).
     */
    public function markAsComplete(string $lessonId, string $userId): ?array
    {
        $lesson = $this->store->find($lessonId);
        if (!$lesson) {
            return null;
        }

        $completedBy = $lesson['completed_by'] ?? [];
        if (!in_array($userId, $completedBy, true)) {
            $completedBy[] = $userId;
        }

        return $this->store->update($lessonId, ['completed_by' => $completedBy]);
    }

    /**
     * Get course progress for a user.
     *
     * @return array{completed: int, total: int, percentage: float}
     */
    public function getProgress(string $courseId, string $userId): array
    {
        $lessons   = $this->getLessonsByCourse($courseId);
        $total     = count($lessons);
        $completed = 0;

        foreach ($lessons as $lesson) {
            $completedBy = $lesson['completed_by'] ?? [];
            if (in_array($userId, $completedBy, true)) {
                $completed++;
            }
        }

        return [
            'completed'  => $completed,
            'total'      => $total,
            'percentage' => $total > 0 ? round(($completed / $total) * 100, 1) : 0.0,
        ];
    }
}
