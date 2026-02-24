<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for direct messaging between users.
 */
class MessageService
{
    private JsonStore $messageStore;
    private JsonStore $conversationStore;

    public function __construct()
    {
        $this->messageStore      = new JsonStore('messages.json');
        $this->conversationStore = new JsonStore('conversations.json');
    }

    /**
     * Get all conversations a user participates in, most recent first.
     */
    public function getConversations(string $userId): array
    {
        $convos = array_values(array_filter(
            $this->conversationStore->readAll(),
            function (array $c) use ($userId) {
                $participants = $c['participants'] ?? [];
                return in_array($userId, $participants, true);
            }
        ));

        usort($convos, fn($a, $b) => strcmp($b['updated_at'] ?? '', $a['updated_at'] ?? ''));

        return $convos;
    }

    /**
     * Get a single conversation by ID.
     */
    public function getConversation(string $id): ?array
    {
        return $this->conversationStore->find($id);
    }

    /**
     * Get or create a 1-to-1 conversation between two users.
     */
    public function getOrCreateConversation(string $userId1, string $userId2): array
    {
        foreach ($this->conversationStore->readAll() as $convo) {
            $participants = $convo['participants'] ?? [];
            if (count($participants) === 2
                && in_array($userId1, $participants, true)
                && in_array($userId2, $participants, true)) {
                return $convo;
            }
        }

        return $this->conversationStore->create([
            'participants' => [$userId1, $userId2],
            'last_message' => null,
            'unread'       => [$userId1 => 0, $userId2 => 0],
        ]);
    }

    /**
     * Send a message within a conversation.
     */
    public function sendMessage(string $conversationId, string $senderId, string $content): array
    {
        $convo = $this->conversationStore->find($conversationId);
        if (!$convo) {
            throw new \RuntimeException('Conversation not found.');
        }

        $message = $this->messageStore->create([
            'conversation_id' => $conversationId,
            'sender_id'       => $senderId,
            'content'         => $content,
            'read_by'         => [$senderId],
        ]);

        // Update conversation metadata
        $unread = $convo['unread'] ?? [];
        foreach ($convo['participants'] ?? [] as $pid) {
            if ($pid !== $senderId) {
                $unread[$pid] = ($unread[$pid] ?? 0) + 1;
            }
        }

        $this->conversationStore->update($conversationId, [
            'last_message' => $content,
            'unread'       => $unread,
        ]);

        return $message;
    }

    /**
     * Get paginated messages for a conversation (newest first).
     */
    public function getMessages(string $conversationId, int $page = 1, int $perPage = 50): array
    {
        $messages = $this->messageStore->findBy('conversation_id', $conversationId);

        usort($messages, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $total  = count($messages);
        $offset = ($page - 1) * $perPage;

        return [
            'messages' => array_slice($messages, $offset, $perPage),
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / max($perPage, 1)),
        ];
    }

    /**
     * Mark all messages in a conversation as read for a user.
     */
    public function markAsRead(string $conversationId, string $userId): void
    {
        $convo = $this->conversationStore->find($conversationId);
        if (!$convo) {
            return;
        }

        // Reset unread counter
        $unread = $convo['unread'] ?? [];
        $unread[$userId] = 0;
        $this->conversationStore->update($conversationId, ['unread' => $unread]);

        // Mark individual messages
        foreach ($this->messageStore->findBy('conversation_id', $conversationId) as $msg) {
            $readBy = $msg['read_by'] ?? [];
            if (!in_array($userId, $readBy, true)) {
                $readBy[] = $userId;
                $this->messageStore->update($msg['id'], ['read_by' => $readBy]);
            }
        }
    }

    /**
     * Get total unread message count across all conversations.
     */
    public function getUnreadCount(string $userId): int
    {
        $count = 0;
        foreach ($this->getConversations($userId) as $convo) {
            $count += (int) ($convo['unread'][$userId] ?? 0);
        }

        return $count;
    }

    /**
     * Delete a conversation and its messages.
     */
    public function deleteConversation(string $id): bool
    {
        if (!$this->conversationStore->delete($id)) {
            return false;
        }

        foreach ($this->messageStore->findBy('conversation_id', $id) as $msg) {
            $this->messageStore->delete($msg['id']);
        }

        return true;
    }
}
