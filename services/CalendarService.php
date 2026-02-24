<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for calendar event management and academic date imports.
 */
class CalendarService
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore('calendar_events.json');
    }

    /**
     * Get all events for a user.
     */
    public function getEventsByUser(string $userId): array
    {
        return $this->store->findBy('user_id', $userId);
    }

    /**
     * Get events for a user on a specific date (Y-m-d).
     */
    public function getEventsByDate(string $userId, string $date): array
    {
        return array_values(array_filter(
            $this->store->findBy('user_id', $userId),
            fn(array $e) => substr($e['date'] ?? '', 0, 10) === $date
        ));
    }

    /**
     * Get events for a user within a given month.
     */
    public function getEventsByMonth(string $userId, int $year, int $month): array
    {
        $prefix = sprintf('%04d-%02d', $year, $month);

        return array_values(array_filter(
            $this->store->findBy('user_id', $userId),
            fn(array $e) => str_starts_with($e['date'] ?? '', $prefix)
        ));
    }

    /**
     * Create a calendar event.
     *
     * @param array $data Must include 'user_id', 'title', 'date'. Optional: 'description', 'type', 'time'.
     */
    public function createEvent(array $data): array
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new \InvalidArgumentException('Event title is required.');
        }

        $record = [
            'user_id'     => $data['user_id'] ?? '',
            'title'       => trim($data['title']),
            'description' => $data['description'] ?? '',
            'date'        => $data['date'] ?? gmdate('Y-m-d'),
            'time'        => $data['time'] ?? null,
            'type'        => $data['type'] ?? 'personal',
            'source_id'   => $data['source_id'] ?? null,
        ];

        return $this->store->create($record);
    }

    /**
     * Update an event.
     */
    public function updateEvent(string $id, array $data): ?array
    {
        if (!$this->store->find($id)) {
            return null;
        }

        return $this->store->update($id, $data);
    }

    /**
     * Delete an event.
     */
    public function deleteEvent(string $id): bool
    {
        return $this->store->delete($id);
    }

    /**
     * Get upcoming events ordered by date, limited to $limit.
     */
    public function getUpcomingEvents(string $userId, int $limit = 5): array
    {
        $now    = gmdate('Y-m-d');
        $events = array_values(array_filter(
            $this->store->findBy('user_id', $userId),
            fn(array $e) => ($e['date'] ?? '') >= $now
        ));

        usort($events, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));

        return array_slice($events, 0, $limit);
    }

    /**
     * Import assignment due dates as calendar events for the user.
     * Skips assignments that already have a corresponding event.
     */
    public function importAssignmentDueDates(string $userId): int
    {
        $assignmentStore = new JsonStore('assignments.json');
        $courseStore      = new JsonStore('courses.json');

        // Determine enrolled course IDs
        $enrolledCourseIds = [];
        foreach ($courseStore->readAll() as $course) {
            if (in_array($userId, $course['enrolled_students'] ?? [], true)) {
                $enrolledCourseIds[] = $course['id'];
            }
        }

        $existingSourceIds = array_column($this->store->findBy('user_id', $userId), 'source_id');
        $imported = 0;

        foreach ($assignmentStore->readAll() as $assignment) {
            if (!in_array($assignment['course_id'] ?? '', $enrolledCourseIds, true)) {
                continue;
            }
            if (empty($assignment['due_date'])) {
                continue;
            }
            $sourceId = 'assignment_' . $assignment['id'];
            if (in_array($sourceId, $existingSourceIds, true)) {
                continue;
            }

            $this->createEvent([
                'user_id'     => $userId,
                'title'       => 'Due: ' . ($assignment['title'] ?? 'Assignment'),
                'date'        => substr($assignment['due_date'], 0, 10),
                'type'        => 'assignment',
                'source_id'   => $sourceId,
                'description' => $assignment['description'] ?? '',
            ]);
            $imported++;
        }

        return $imported;
    }

    /**
     * Import quiz dates as calendar events for the user.
     * Skips quizzes that already have a corresponding event.
     */
    public function importQuizDates(string $userId): int
    {
        $quizStore   = new JsonStore('quizzes.json');
        $courseStore  = new JsonStore('courses.json');

        $enrolledCourseIds = [];
        foreach ($courseStore->readAll() as $course) {
            if (in_array($userId, $course['enrolled_students'] ?? [], true)) {
                $enrolledCourseIds[] = $course['id'];
            }
        }

        $existingSourceIds = array_column($this->store->findBy('user_id', $userId), 'source_id');
        $imported = 0;

        foreach ($quizStore->readAll() as $quiz) {
            if (!in_array($quiz['course_id'] ?? '', $enrolledCourseIds, true)) {
                continue;
            }
            if (empty($quiz['due_date'])) {
                continue;
            }
            $sourceId = 'quiz_' . $quiz['id'];
            if (in_array($sourceId, $existingSourceIds, true)) {
                continue;
            }

            $this->createEvent([
                'user_id'     => $userId,
                'title'       => 'Quiz: ' . ($quiz['title'] ?? 'Quiz'),
                'date'        => substr($quiz['due_date'], 0, 10),
                'type'        => 'quiz',
                'source_id'   => $sourceId,
                'description' => $quiz['description'] ?? '',
            ]);
            $imported++;
        }

        return $imported;
    }
}
