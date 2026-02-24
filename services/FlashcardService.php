<?php

namespace Services;

use Core\JsonStore;

/**
 * Service for flashcard set management and study progress tracking.
 */
class FlashcardService
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore('flashcard_sets.json');
    }

    /**
     * Get all flashcard sets for a user.
     */
    public function getSetsByUser(string $userId): array
    {
        return $this->store->findBy('user_id', $userId);
    }

    /**
     * Get a single set by ID.
     */
    public function getSetById(string $id): ?array
    {
        return $this->store->find($id);
    }

    /**
     * Create a flashcard set.
     *
     * @param array $data Must include 'user_id', 'title', 'cards'. Each card: { front, back }.
     */
    public function createSet(array $data): array
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new \InvalidArgumentException('Set title is required.');
        }

        $cards = [];
        foreach ($data['cards'] ?? [] as $card) {
            $cards[] = [
                'front' => $card['front'] ?? '',
                'back'  => $card['back'] ?? '',
            ];
        }

        $record = [
            'user_id'    => $data['user_id'] ?? '',
            'title'      => trim($data['title']),
            'description' => $data['description'] ?? '',
            'subject'    => $data['subject'] ?? '',
            'cards'      => $cards,
            'is_public'  => (bool) ($data['is_public'] ?? false),
            'study_progress' => [],
        ];

        return $this->store->create($record);
    }

    /**
     * Update set fields.
     */
    public function updateSet(string $id, array $data): ?array
    {
        if (!$this->store->find($id)) {
            return null;
        }

        return $this->store->update($id, $data);
    }

    /**
     * Delete a flashcard set.
     */
    public function deleteSet(string $id): bool
    {
        return $this->store->delete($id);
    }

    /**
     * Add a single card to an existing set.
     */
    public function addCard(string $setId, array $card): ?array
    {
        $set = $this->store->find($setId);
        if (!$set) {
            return null;
        }

        $cards   = $set['cards'] ?? [];
        $cards[] = [
            'front' => $card['front'] ?? '',
            'back'  => $card['back'] ?? '',
        ];

        return $this->store->update($setId, ['cards' => $cards]);
    }

    /**
     * Remove a card by its index.
     */
    public function removeCard(string $setId, int $cardIndex): ?array
    {
        $set = $this->store->find($setId);
        if (!$set) {
            return null;
        }

        $cards = $set['cards'] ?? [];
        if (!isset($cards[$cardIndex])) {
            return $set;
        }

        array_splice($cards, $cardIndex, 1);

        return $this->store->update($setId, ['cards' => $cards]);
    }

    /**
     * Record whether a user answered a card correctly.
     *
     * study_progress is stored as a map: { <userId>: { <cardIndex>: { correct, incorrect } } }
     */
    public function updateStudyProgress(string $setId, string $userId, int $cardIndex, bool $correct): ?array
    {
        $set = $this->store->find($setId);
        if (!$set) {
            return null;
        }

        $progress = $set['study_progress'] ?? [];

        if (!isset($progress[$userId])) {
            $progress[$userId] = [];
        }
        if (!isset($progress[$userId][$cardIndex])) {
            $progress[$userId][$cardIndex] = ['correct' => 0, 'incorrect' => 0];
        }

        if ($correct) {
            $progress[$userId][$cardIndex]['correct']++;
        } else {
            $progress[$userId][$cardIndex]['incorrect']++;
        }

        return $this->store->update($setId, ['study_progress' => $progress]);
    }

    /**
     * Get aggregated study stats for a user on a set.
     *
     * @return array{total_cards: int, studied: int, correct: int, incorrect: int, mastery: float}
     */
    public function getStudyStats(string $setId, string $userId): array
    {
        $set = $this->store->find($setId);
        if (!$set) {
            return ['total_cards' => 0, 'studied' => 0, 'correct' => 0, 'incorrect' => 0, 'mastery' => 0.0];
        }

        $totalCards = count($set['cards'] ?? []);
        $progress   = $set['study_progress'][$userId] ?? [];

        $studied   = count($progress);
        $correct   = 0;
        $incorrect = 0;

        foreach ($progress as $stats) {
            $correct   += $stats['correct'] ?? 0;
            $incorrect += $stats['incorrect'] ?? 0;
        }

        $totalAttempts = $correct + $incorrect;

        return [
            'total_cards' => $totalCards,
            'studied'     => $studied,
            'correct'     => $correct,
            'incorrect'   => $incorrect,
            'mastery'     => $totalAttempts > 0 ? round(($correct / $totalAttempts) * 100, 1) : 0.0,
        ];
    }

    /**
     * Get all publicly shared sets.
     */
    public function getPublicSets(): array
    {
        return $this->store->findBy('is_public', true);
    }
}
