<?php
$set = $set ?? null;
$subjects = $subjects ?? [];
$errors = $errors ?? [];
$isEditing = !empty($set);
$existingCards = $set['cards'] ?? [];
?>

<div class="max-w-4xl mx-auto">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="<?= $isEditing ? '/flashcards/' . (int)$set['id'] : '/flashcards' ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="fas fa-arrow-left"></i> <?= $isEditing ? 'Back to Set' : 'Back to Flashcard Sets' ?>
        </a>
    </div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= $isEditing ? 'Edit Flashcard Set' : 'Create Flashcard Set' ?></h1>
        <p class="mt-1 text-gray-500"><?= $isEditing ? 'Update your flashcard set and cards' : 'Build a new set of flashcards to study' ?></p>
    </div>

    <!-- Errors -->
    <?php if (!empty($errors)): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex items-center gap-2 text-red-700 font-semibold text-sm mb-1">
                <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="<?= $isEditing ? '/flashcards/' . (int)$set['id'] . '/edit' : '/flashcards/create' ?>" id="flashcard-set-form">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

        <!-- Set Details -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-info-circle text-indigo-500"></i> Set Details
            </h2>

            <div>
                <label for="title" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Title</label>
                <input type="text" id="title" name="title" required
                    value="<?= htmlspecialchars($set['title'] ?? '') ?>"
                    placeholder="e.g., Biology Chapter 5 Vocabulary"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
            </div>

            <div>
                <label for="description" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Description</label>
                <textarea id="description" name="description" rows="2" placeholder="Optional description of this flashcard set..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700 resize-none"><?= htmlspecialchars($set['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="subject" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Subject</label>
                    <select id="subject" name="subject" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                        <option value="">Select a subject</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?= htmlspecialchars($s) ?>" <?= ($set['subject'] ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-3 cursor-pointer px-4 py-2.5">
                        <input type="checkbox" name="is_public" value="1"
                            class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2"
                            <?= !empty($set['is_public']) ? 'checked' : '' ?>>
                        <div>
                            <span class="text-sm font-semibold text-gray-700">Make Public</span>
                            <p class="text-xs text-gray-400">Other students can find and study this set</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Cards Builder -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-clone text-indigo-500"></i> Cards
                    <span id="card-count-badge" class="ml-2 px-2.5 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold"><?= max(count($existingCards), 3) ?></span>
                </h2>
                <button type="button" id="add-card-btn" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-sm">
                    <i class="fas fa-plus"></i> Add Card
                </button>
            </div>

            <div id="cards-container" class="space-y-4">
                <?php
                $cardsToShow = !empty($existingCards) ? $existingCards : [['front' => '', 'back' => ''], ['front' => '', 'back' => ''], ['front' => '', 'back' => '']];
                foreach ($cardsToShow as $i => $card):
                ?>
                    <div class="card-row group border border-gray-100 rounded-xl p-4 hover:border-indigo-100 transition-colors" data-index="<?= $i ?>">
                        <div class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-500 mt-1 card-number"><?= $i + 1 ?></span>
                            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Front (Term)</label>
                                    <input type="text" name="cards[<?= $i ?>][front]" value="<?= htmlspecialchars($card['front'] ?? '') ?>"
                                        placeholder="Enter term..."
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Back (Definition)</label>
                                    <input type="text" name="cards[<?= $i ?>][back]" value="<?= htmlspecialchars($card['back'] ?? '') ?>"
                                        placeholder="Enter definition..."
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700">
                                </div>
                            </div>
                            <button type="button" class="remove-card-btn flex-shrink-0 w-8 h-8 rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors mt-1 opacity-0 group-hover:opacity-100" title="Remove card">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                <i class="fas fa-save"></i> <?= $isEditing ? 'Update Set' : 'Create Set' ?>
            </button>
            <a href="<?= $isEditing ? '/flashcards/' . (int)$set['id'] : '/flashcards' ?>" class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-center">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('cards-container');
    const addBtn = document.getElementById('add-card-btn');
    const badge = document.getElementById('card-count-badge');

    function updateNumbers() {
        const rows = container.querySelectorAll('.card-row');
        rows.forEach((row, idx) => {
            row.dataset.index = idx;
            row.querySelector('.card-number').textContent = idx + 1;
            row.querySelectorAll('input[type="text"]').forEach(input => {
                const field = input.name.includes('[front]') ? 'front' : 'back';
                input.name = `cards[${idx}][${field}]`;
            });
        });
        badge.textContent = rows.length;
    }

    addBtn.addEventListener('click', function() {
        const idx = container.querySelectorAll('.card-row').length;
        const row = document.createElement('div');
        row.className = 'card-row group border border-gray-100 rounded-xl p-4 hover:border-indigo-100 transition-colors';
        row.dataset.index = idx;
        row.innerHTML = `
            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-500 mt-1 card-number">${idx + 1}</span>
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Front (Term)</label>
                        <input type="text" name="cards[${idx}][front]" placeholder="Enter term..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Back (Definition)</label>
                        <input type="text" name="cards[${idx}][back]" placeholder="Enter definition..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700">
                    </div>
                </div>
                <button type="button" class="remove-card-btn flex-shrink-0 w-8 h-8 rounded-lg bg-red-50 text-red-400 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition-colors mt-1 opacity-0 group-hover:opacity-100" title="Remove card">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>`;
        container.appendChild(row);
        updateNumbers();
        row.querySelector('input').focus();
    });

    container.addEventListener('click', function(e) {
        const btn = e.target.closest('.remove-card-btn');
        if (!btn) return;
        if (container.querySelectorAll('.card-row').length <= 1) return;
        btn.closest('.card-row').remove();
        updateNumbers();
    });
});
</script>
