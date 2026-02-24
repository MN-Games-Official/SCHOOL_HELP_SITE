<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for user account management.
 */
class UserService
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore('users.json');
    }

    /**
     * Find a user by email address.
     */
    public function findByEmail(string $email): ?array
    {
        $results = $this->store->findBy('email', strtolower(trim($email)));
        return $results[0] ?? null;
    }

    /**
     * Find a user by ID.
     */
    public function findById(string $id): ?array
    {
        return $this->store->find($id);
    }

    /**
     * Create a new user with hashed password, role and timestamps.
     *
     * @param array $data Must contain 'email', 'password', 'name'. Optional: 'role'.
     * @return array The created user record (without plain password).
     * @throws \RuntimeException If email is already taken.
     */
    public function createUser(array $data): array
    {
        $email = strtolower(trim($data['email'] ?? ''));
        if ($email === '') {
            throw new \InvalidArgumentException('Email is required.');
        }

        if ($this->findByEmail($email)) {
            throw new \RuntimeException('A user with this email already exists.');
        }

        $validRoles = ['student', 'teacher', 'admin'];
        $role = in_array($data['role'] ?? '', $validRoles, true) ? $data['role'] : 'student';

        $record = [
            'name'     => trim($data['name'] ?? ''),
            'email'    => $email,
            'password' => password_hash($data['password'] ?? '', PASSWORD_BCRYPT),
            'role'     => $role,
            'avatar'   => $data['avatar'] ?? null,
            'bio'      => $data['bio'] ?? '',
            'active'   => true,
        ];

        return $this->store->create($record);
    }

    /**
     * Update user fields (password is NOT updated here).
     */
    public function updateUser(string $id, array $data): ?array
    {
        $user = $this->store->find($id);
        if (!$user) {
            return null;
        }

        // Prevent email collision when changing email
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
            $existing = $this->findByEmail($data['email']);
            if ($existing && $existing['id'] !== $id) {
                throw new \RuntimeException('Email is already taken by another user.');
            }
        }

        // Never allow password update through this method
        unset($data['password']);

        return $this->store->update($id, $data);
    }

    /**
     * Delete a user by ID.
     */
    public function deleteUser(string $id): bool
    {
        return $this->store->delete($id);
    }

    /**
     * Return every user record.
     */
    public function getAllUsers(): array
    {
        return $this->store->readAll();
    }

    /**
     * Return users filtered by role.
     */
    public function getUsersByRole(string $role): array
    {
        return $this->store->findBy('role', $role);
    }

    /**
     * Update a user's password (hashes the new value).
     */
    public function updatePassword(string $id, string $newPassword): ?array
    {
        if (strlen($newPassword) < 6) {
            throw new \InvalidArgumentException('Password must be at least 6 characters.');
        }

        return $this->store->update($id, [
            'password' => password_hash($newPassword, PASSWORD_BCRYPT),
        ]);
    }

    /**
     * Aggregate stats: courses enrolled, assignments submitted, quizzes taken.
     */
    public function getStats(string $userId): array
    {
        $courses     = new JsonStore('courses.json');
        $submissions = new JsonStore('submissions.json');
        $attempts    = new JsonStore('quiz_attempts.json');

        $enrolledCount = 0;
        foreach ($courses->readAll() as $course) {
            $enrolled = $course['enrolled_students'] ?? [];
            if (in_array($userId, $enrolled, true)) {
                $enrolledCount++;
            }
        }

        $submittedCount = count(
            $submissions->findBy('student_id', $userId)
        );

        $quizzesTaken = count(
            $attempts->findBy('user_id', $userId)
        );

        return [
            'courses_enrolled'      => $enrolledCount,
            'assignments_submitted' => $submittedCount,
            'quizzes_taken'         => $quizzesTaken,
        ];
    }

    /**
     * Search users by name or email.
     */
    public function searchUsers(string $query): array
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return [];
        }

        return array_values(array_filter(
            $this->store->readAll(),
            function (array $user) use ($query) {
                return str_contains(mb_strtolower($user['name'] ?? ''), $query)
                    || str_contains(mb_strtolower($user['email'] ?? ''), $query);
            }
        ));
    }
}
