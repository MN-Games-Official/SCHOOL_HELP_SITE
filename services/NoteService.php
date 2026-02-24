<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for personal note management and sharing.
 */
class NoteService
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore('notes.json');
    }

    /**
     * Get all notes belonging to a user.
     */
    public function getNotesByUser(string $userId): array
    {
        return $this->store->findBy('user_id', $userId);
    }

    /**
     * Get a single note by ID.
     */
    public function getNoteById(string $id): ?array
    {
        return $this->store->find($id);
    }

    /**
     * Create a new note.
     *
     * @param array $data Must include 'user_id', 'title'. Optional: 'content', 'subject', 'tags'.
     */
    public function createNote(array $data): array
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new \InvalidArgumentException('Note title is required.');
        }

        $record = [
            'user_id'   => $data['user_id'] ?? '',
            'title'     => trim($data['title']),
            'content'   => $data['content'] ?? '',
            'subject'   => $data['subject'] ?? '',
            'tags'      => $data['tags'] ?? [],
            'shared_with' => [],
        ];

        return $this->store->create($record);
    }

    /**
     * Update note fields.
     */
    public function updateNote(string $id, array $data): ?array
    {
        if (!$this->store->find($id)) {
            return null;
        }

        return $this->store->update($id, $data);
    }

    /**
     * Delete a note.
     */
    public function deleteNote(string $id): bool
    {
        return $this->store->delete($id);
    }

    /**
     * Search a user's notes by title and content.
     */
    public function searchNotes(string $userId, string $query): array
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return [];
        }

        return array_values(array_filter(
            $this->store->findBy('user_id', $userId),
            function (array $note) use ($query) {
                return str_contains(mb_strtolower($note['title'] ?? ''), $query)
                    || str_contains(mb_strtolower($note['content'] ?? ''), $query);
            }
        ));
    }

    /**
     * Get notes filtered by subject.
     */
    public function getNotesBySubject(string $userId, string $subject): array
    {
        return array_values(array_filter(
            $this->store->findBy('user_id', $userId),
            fn(array $note) => ($note['subject'] ?? '') === $subject
        ));
    }

    /**
     * Share a note with another user (idempotent).
     */
    public function shareNote(string $noteId, string $userId): ?array
    {
        $note = $this->store->find($noteId);
        if (!$note) {
            return null;
        }

        $sharedWith = $note['shared_with'] ?? [];
        if (!in_array($userId, $sharedWith, true)) {
            $sharedWith[] = $userId;
        }

        return $this->store->update($noteId, ['shared_with' => $sharedWith]);
    }

    /**
     * Get notes shared with a user by others.
     */
    public function getSharedNotes(string $userId): array
    {
        return array_values(array_filter(
            $this->store->readAll(),
            function (array $note) use ($userId) {
                $sharedWith = $note['shared_with'] ?? [];
                return in_array($userId, $sharedWith, true);
            }
        ));
    }
}
