<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for quiz management, attempt submission and grading.
 */
class QuizService
{
    private JsonStore $quizStore;
    private JsonStore $attemptStore;

    public function __construct()
    {
        $this->quizStore    = new JsonStore('quizzes.json');
        $this->attemptStore = new JsonStore('quiz_attempts.json');
    }

    /**
     * Return every quiz.
     */
    public function getAllQuizzes(): array
    {
        return $this->quizStore->readAll();
    }

    /**
     * Get a single quiz by ID.
     */
    public function getQuizById(string $id): ?array
    {
        return $this->quizStore->find($id);
    }

    /**
     * Quizzes belonging to a course.
     */
    public function getQuizzesByCourse(string $courseId): array
    {
        return $this->quizStore->findBy('course_id', $courseId);
    }

    /**
     * Quizzes created by a teacher.
     */
    public function getQuizzesByTeacher(string $teacherId): array
    {
        return $this->quizStore->findBy('teacher_id', $teacherId);
    }

    /**
     * Create a quiz.
     *
     * @param array $data Must include 'title', 'course_id', 'teacher_id', 'questions'.
     *                     Each question: { question, options[], correct_answer }
     */
    public function createQuiz(array $data): array
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new \InvalidArgumentException('Quiz title is required.');
        }

        $questions = $data['questions'] ?? [];
        if (empty($questions)) {
            throw new \InvalidArgumentException('At least one question is required.');
        }

        // Normalize questions
        $normalised = [];
        foreach ($questions as $i => $q) {
            $normalised[] = [
                'question'       => $q['question'] ?? '',
                'options'        => $q['options'] ?? [],
                'correct_answer' => $q['correct_answer'] ?? 0,
            ];
        }

        $record = [
            'title'       => trim($data['title']),
            'description' => trim($data['description'] ?? ''),
            'course_id'   => $data['course_id'] ?? '',
            'teacher_id'  => $data['teacher_id'] ?? '',
            'questions'   => $normalised,
            'time_limit'  => $data['time_limit'] ?? null,
            'max_attempts' => $data['max_attempts'] ?? 1,
            'status'      => $data['status'] ?? 'active',
            'due_date'    => $data['due_date'] ?? null,
        ];

        return $this->quizStore->create($record);
    }

    /**
     * Update quiz fields.
     */
    public function updateQuiz(string $id, array $data): ?array
    {
        if (!$this->quizStore->find($id)) {
            return null;
        }

        return $this->quizStore->update($id, $data);
    }

    /**
     * Delete a quiz and its attempts.
     */
    public function deleteQuiz(string $id): bool
    {
        if (!$this->quizStore->delete($id)) {
            return false;
        }

        foreach ($this->attemptStore->findBy('quiz_id', $id) as $attempt) {
            $this->attemptStore->delete($attempt['id']);
        }

        return true;
    }

    /**
     * Submit and auto-grade a quiz attempt.
     *
     * @param string $quizId
     * @param string $userId
     * @param array  $answers Map of question-index => selected answer index.
     * @return array The stored attempt with score.
     */
    public function submitAttempt(string $quizId, string $userId, array $answers): array
    {
        $quiz = $this->quizStore->find($quizId);
        if (!$quiz) {
            throw new \RuntimeException('Quiz not found.');
        }

        // Enforce max attempts
        $previous = $this->getAttempts($quizId, $userId);
        $maxAttempts = $quiz['max_attempts'] ?? 1;
        if ($maxAttempts > 0 && count($previous) >= $maxAttempts) {
            throw new \RuntimeException('Maximum number of attempts reached.');
        }

        $scoreData = $this->calculateScore($quiz, $answers);

        $attempt = [
            'quiz_id'    => $quizId,
            'user_id'    => $userId,
            'answers'    => $answers,
            'score'      => $scoreData['score'],
            'total'      => $scoreData['total'],
            'percentage' => $scoreData['percentage'],
            'details'    => $scoreData['details'],
        ];

        return $this->attemptStore->create($attempt);
    }

    /**
     * Get all attempts by a user for a specific quiz.
     */
    public function getAttempts(string $quizId, string $userId): array
    {
        return array_values(array_filter(
            $this->attemptStore->readAll(),
            fn(array $a) => ($a['quiz_id'] ?? '') === $quizId
                         && ($a['user_id'] ?? '') === $userId
        ));
    }

    /**
     * Get every attempt/result for a quiz (all students).
     */
    public function getQuizResults(string $quizId): array
    {
        return $this->attemptStore->findBy('quiz_id', $quizId);
    }

    /**
     * Compare submitted answers against correct answers and compute score.
     *
     * @return array{score: int, total: int, percentage: float, details: array}
     */
    public function calculateScore(array $quiz, array $answers): array
    {
        $questions = $quiz['questions'] ?? [];
        $total     = count($questions);
        $score     = 0;
        $details   = [];

        foreach ($questions as $index => $question) {
            $correct  = $question['correct_answer'] ?? 0;
            $given    = $answers[$index] ?? null;
            $isRight  = $given !== null && (int) $given === (int) $correct;

            if ($isRight) {
                $score++;
            }

            $details[] = [
                'question_index' => $index,
                'given'          => $given,
                'correct'        => $correct,
                'is_correct'     => $isRight,
            ];
        }

        return [
            'score'      => $score,
            'total'      => $total,
            'percentage' => $total > 0 ? round(($score / $total) * 100, 1) : 0.0,
            'details'    => $details,
        ];
    }
}
