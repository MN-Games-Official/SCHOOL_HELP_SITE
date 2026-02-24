<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for discussion forum threads and replies.
 */
class ForumService
{
    private JsonStore $threadStore;
    private JsonStore $replyStore;

    public function __construct()
    {
        $this->threadStore = new JsonStore('forum_threads.json');
        $this->replyStore  = new JsonStore('forum_replies.json');
    }

    /**
     * Get a paginated list of threads, pinned first, then by creation date desc.
     */
    public function getAllThreads(int $page = 1, int $perPage = 20): array
    {
        $threads = $this->threadStore->readAll();

        usort($threads, function (array $a, array $b) {
            $pinA = (int) ($a['is_pinned'] ?? 0);
            $pinB = (int) ($b['is_pinned'] ?? 0);
            if ($pinA !== $pinB) {
                return $pinB <=> $pinA;
            }
            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
        });

        $total  = count($threads);
        $offset = ($page - 1) * $perPage;

        return [
            'threads'  => array_slice($threads, $offset, $perPage),
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Get a single thread by ID.
     */
    public function getThreadById(string $id): ?array
    {
        return $this->threadStore->find($id);
    }

    /**
     * Filter threads by category.
     */
    public function getThreadsByCategory(string $category): array
    {
        return $this->threadStore->findBy('category', $category);
    }

    /**
     * Create a new thread.
     *
     * @param array $data Must include 'title', 'user_id'. Optional: 'content', 'category'.
     */
    public function createThread(array $data): array
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new \InvalidArgumentException('Thread title is required.');
        }

        $record = [
            'title'     => trim($data['title']),
            'content'   => $data['content'] ?? '',
            'user_id'   => $data['user_id'] ?? '',
            'category'  => $data['category'] ?? 'general',
            'is_pinned' => false,
            'views'     => 0,
        ];

        return $this->threadStore->create($record);
    }

    /**
     * Delete a thread and its replies.
     */
    public function deleteThread(string $id): bool
    {
        if (!$this->threadStore->delete($id)) {
            return false;
        }

        foreach ($this->replyStore->findBy('thread_id', $id) as $reply) {
            $this->replyStore->delete($reply['id']);
        }

        return true;
    }

    /**
     * Add a reply to a thread.
     */
    public function addReply(string $threadId, array $data): array
    {
        $thread = $this->threadStore->find($threadId);
        if (!$thread) {
            throw new \RuntimeException('Thread not found.');
        }

        $record = [
            'thread_id' => $threadId,
            'user_id'   => $data['user_id'] ?? '',
            'content'   => $data['content'] ?? '',
        ];

        return $this->replyStore->create($record);
    }

    /**
     * Delete a reply.
     */
    public function deleteReply(string $replyId): bool
    {
        return $this->replyStore->delete($replyId);
    }

    /**
     * Get all replies for a thread ordered by creation date.
     */
    public function getReplies(string $threadId): array
    {
        $replies = $this->replyStore->findBy('thread_id', $threadId);

        usort($replies, fn($a, $b) => strcmp($a['created_at'] ?? '', $b['created_at'] ?? ''));

        return $replies;
    }

    /**
     * Increment the view counter on a thread.
     */
    public function incrementViews(string $threadId): ?array
    {
        $thread = $this->threadStore->find($threadId);
        if (!$thread) {
            return null;
        }

        return $this->threadStore->update($threadId, [
            'views' => ($thread['views'] ?? 0) + 1,
        ]);
    }

    /**
     * Search threads by title and content.
     */
    public function searchThreads(string $query): array
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return [];
        }

        return array_values(array_filter(
            $this->threadStore->readAll(),
            function (array $thread) use ($query) {
                return str_contains(mb_strtolower($thread['title'] ?? ''), $query)
                    || str_contains(mb_strtolower($thread['content'] ?? ''), $query);
            }
        ));
    }

    /**
     * Pin a thread.
     */
    public function pinThread(string $threadId): ?array
    {
        return $this->threadStore->update($threadId, ['is_pinned' => true]);
    }

    /**
     * Unpin a thread.
     */
    public function unpinThread(string $threadId): ?array
    {
        return $this->threadStore->update($threadId, ['is_pinned' => false]);
    }
}
