<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for user notifications.
 */
class NotificationService
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore('notifications.json');
    }

    /**
     * Create a new notification for a user.
     *
     * @param string      $userId
     * @param string      $type    e.g. 'assignment', 'quiz', 'grade', 'message', 'system'.
     * @param string      $message Human-readable notification text.
     * @param string|null $link    Optional URL or route to link to.
     * @return array The created notification record.
     */
    public function create(string $userId, string $type, string $message, ?string $link = null): array
    {
        return $this->store->create([
            'user_id' => $userId,
            'type'    => $type,
            'message' => $message,
            'link'    => $link,
            'is_read' => false,
        ]);
    }

    /**
     * Get all notifications for a user (newest first).
     */
    public function getForUser(string $userId): array
    {
        $notifs = $this->store->findBy('user_id', $userId);

        usort($notifs, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        return $notifs;
    }

    /**
     * Get unread notifications for a user.
     */
    public function getUnread(string $userId): array
    {
        return array_values(array_filter(
            $this->getForUser($userId),
            fn(array $n) => !($n['is_read'] ?? true)
        ));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(string $id): ?array
    {
        $notif = $this->store->find($id);
        if (!$notif) {
            return null;
        }

        return $this->store->update($id, ['is_read' => true]);
    }

    /**
     * Mark all notifications for a user as read.
     */
    public function markAllAsRead(string $userId): int
    {
        $count = 0;
        foreach ($this->getUnread($userId) as $notif) {
            $this->store->update($notif['id'], ['is_read' => true]);
            $count++;
        }

        return $count;
    }

    /**
     * Delete notifications older than the specified number of days.
     *
     * @return int Number of deleted records.
     */
    public function deleteOld(int $days = 30): int
    {
        $cutoff  = gmdate('Y-m-d\TH:i:s\Z', time() - ($days * 86400));
        $deleted = 0;

        foreach ($this->store->readAll() as $notif) {
            if (($notif['created_at'] ?? '') < $cutoff) {
                $this->store->delete($notif['id']);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Count unread notifications for a user.
     */
    public function getUnreadCount(string $userId): int
    {
        return count($this->getUnread($userId));
    }
}
