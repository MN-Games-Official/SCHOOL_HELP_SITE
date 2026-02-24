<?php
$set = $set ?? [];
$cards = $cards ?? [];
$isOwner = $isOwner ?? false;
$mastery = $set['mastery'] ?? 0;
$masteryColor = $mastery >= 80 ? 'bg-green-500' : ($mastery >= 50 ? 'bg-yellow-500' : 'bg-red-500');
?>

<div class="max-w-4xl mx-auto">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="/flashcards" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="fas fa-arrow-left"></i> Back to Flashcard Sets
        </a>
    </div>

    <!-- Set Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div class="flex-1">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($set['title'] ?? 'Untitled Set') ?></h1>
                <?php if (!empty($set['description'])): ?>
                    <p class="text-gray-500 mb-3"><?= htmlspecialchars($set['description']) ?></p>
                <?php endif; ?>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-clone mr-1.5 text-xs"></i> <?= count($cards) ?> cards
                    </span>
                    <?php if (!empty($set['subject'])): ?>
                        <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-semibold">
                            <i class="fas fa-tag mr-1.5 text-xs"></i> <?= htmlspecialchars($set['subject']) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($set['is_public'])): ?>
                        <span class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm font-semibold">
                            <i class="fas fa-globe mr-1.5 text-xs"></i> Public
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Study Button -->
            <a href="/flashcards/<?= (int)$set['id'] ?>/study" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-md hover:shadow-lg text-lg flex-shrink-0">
                <i class="fas fa-play"></i> Study
            </a>
        </div>

        <!-- Mastery Progress -->
        <div class="mb-4">
            <div class="flex justify-between text-sm mb-1.5">
                <span class="text-gray-500 font-medium">Mastery Progress</span>
                <span class="font-bold text-gray-700"><?= (int)$mastery ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="<?= $masteryColor ?> h-3 rounded-full transition-all duration-500" style="width: <?= (int)$mastery ?>%"></div>
            </div>
        </div>

        <!-- Owner Actions -->
        <?php if ($isOwner): ?>
            <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-gray-100">
                <a href="/flashcards/<?= (int)$set['id'] ?>/edit" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm">
                    <i class="fas fa-pen"></i> Edit Set
                </a>
                <form method="POST" action="/flashcards/<?= (int)$set['id'] ?>/delete" class="inline" onsubmit="return confirm('Are you sure you want to delete this flashcard set?')">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 font-semibold rounded-xl hover:bg-red-100 transition-colors text-sm">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
                <form method="POST" action="/flashcards/<?= (int)$set['id'] ?>/toggle-public" class="inline ml-auto">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition-colors <?= !empty($set['is_public']) ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' ?>">
                        <i class="fas <?= !empty($set['is_public']) ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i>
                        <?= !empty($set['is_public']) ? 'Public' : 'Private' ?>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Cards List -->
    <h2 class="text-lg font-bold text-gray-900 mb-4">All Cards</h2>

    <?php if (empty($cards)): ?>
        <div class="text-center py-12 bg-white rounded-2xl border border-gray-100">
            <i class="fas fa-clone text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No cards in this set yet.</p>
            <?php if ($isOwner): ?>
                <a href="/flashcards/<?= (int)$set['id'] ?>/edit" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-sm mt-4">
                    <i class="fas fa-plus"></i> Add Cards
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th class="text-left py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">#</th>
                            <th class="text-left py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Front (Term)</th>
                            <th class="text-left py-3 px-5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Back (Definition)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($cards as $i => $card): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-5 text-gray-400 font-medium"><?= $i + 1 ?></td>
                                <td class="py-3 px-5 font-medium text-gray-900"><?= htmlspecialchars($card['front'] ?? '') ?></td>
                                <td class="py-3 px-5 text-gray-600"><?= htmlspecialchars($card['back'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
