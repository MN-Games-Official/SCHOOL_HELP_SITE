<?php
$scripts = '/assets/js/flashcards.js';
$set = $set ?? [];
$cards = $cards ?? [];
$totalCards = count($cards);
?>

<style>
    .flashcard-container { perspective: 1000px; }
    .flashcard-inner {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
    }
    .flashcard-inner.flipped { transform: rotateY(180deg); }
    .flashcard-front, .flashcard-back {
        position: absolute;
        inset: 0;
        backface-visibility: hidden;
        -webkit-backface-visibility: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1.5rem;
        padding: 2rem;
    }
    .flashcard-back { transform: rotateY(180deg); }
</style>

<div class="max-w-3xl mx-auto" id="study-app"
     data-cards='<?= htmlspecialchars(json_encode($cards), ENT_QUOTES) ?>'
     data-set-id="<?= (int)($set['id'] ?? 0) ?>">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <a href="/flashcards/<?= (int)($set['id'] ?? 0) ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="fas fa-arrow-left"></i> Back to Set
        </a>
        <h1 class="text-lg font-bold text-gray-900 hidden sm:block"><?= htmlspecialchars($set['title'] ?? 'Study Mode') ?></h1>
        <div id="card-counter" class="text-sm font-semibold text-gray-500">
            <span id="current-card">1</span> of <span id="total-cards"><?= $totalCards ?></span>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="mb-6">
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="progress-bar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: <?= $totalCards > 0 ? round((1 / $totalCards) * 100) : 0 ?>%"></div>
        </div>
    </div>

    <?php if ($totalCards === 0): ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <i class="fas fa-clone text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700">No cards to study</h3>
            <p class="text-gray-500 mt-1">Add some cards to this set first.</p>
        </div>
    <?php else: ?>
        <!-- Flashcard -->
        <div class="flashcard-container mb-6 cursor-pointer" id="flashcard" role="button" aria-label="Click to flip card" tabindex="0" style="height: 320px;">
            <div class="flashcard-inner" id="flashcard-inner">
                <!-- Front -->
                <div class="flashcard-front bg-white shadow-lg border border-gray-100">
                    <div class="text-center w-full">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Term</p>
                        <p id="card-front" class="text-xl sm:text-2xl font-bold text-gray-900 leading-relaxed"><?= htmlspecialchars($cards[0]['front'] ?? '') ?></p>
                        <p class="text-xs text-gray-400 mt-6"><i class="fas fa-sync-alt mr-1"></i> Click or press Space to flip</p>
                    </div>
                </div>
                <!-- Back -->
                <div class="flashcard-back bg-gradient-to-br from-indigo-50 to-purple-50 shadow-lg border border-indigo-100">
                    <div class="text-center w-full">
                        <p class="text-xs font-semibold text-indigo-400 uppercase tracking-wider mb-4">Definition</p>
                        <p id="card-back" class="text-xl sm:text-2xl font-bold text-gray-800 leading-relaxed"><?= htmlspecialchars($cards[0]['back'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div id="study-actions" class="flex items-center justify-center gap-4 mb-8">
            <button id="btn-learning" class="flex-1 max-w-xs flex items-center justify-center gap-2 px-6 py-4 bg-red-50 text-red-600 font-bold rounded-xl hover:bg-red-100 transition-colors border-2 border-red-200 hover:border-red-300">
                <i class="fas fa-times-circle text-lg"></i> Still Learning
            </button>
            <button id="btn-knew" class="flex-1 max-w-xs flex items-center justify-center gap-2 px-6 py-4 bg-green-50 text-green-600 font-bold rounded-xl hover:bg-green-100 transition-colors border-2 border-green-200 hover:border-green-300">
                <i class="fas fa-check-circle text-lg"></i> I Knew It
            </button>
        </div>

        <!-- End of Study Session -->
        <div id="study-complete" class="hidden">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-100 to-emerald-100 flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-trophy text-3xl text-green-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Study Session Complete!</h2>
                <p class="text-gray-500 mb-6">Great job! Here's how you did:</p>

                <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto mb-8">
                    <div class="bg-green-50 rounded-xl p-4">
                        <p class="text-3xl font-extrabold text-green-600" id="knew-count">0</p>
                        <p class="text-xs font-semibold text-green-500 uppercase">Knew It</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4">
                        <p class="text-3xl font-extrabold text-red-600" id="learning-count">0</p>
                        <p class="text-xs font-semibold text-red-500 uppercase">Still Learning</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button id="restart-btn" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-redo"></i> Study Again
                    </button>
                    <a href="/flashcards/<?= (int)($set['id'] ?? 0) ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                        <i class="fas fa-arrow-left"></i> Back to Set
                    </a>
                </div>
            </div>
        </div>

        <!-- Keyboard Shortcuts -->
        <div class="text-center">
            <div class="inline-flex items-center gap-4 text-xs text-gray-400 bg-gray-50 rounded-xl px-4 py-2">
                <span><kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded text-gray-500 font-mono">Space</kbd> Flip</span>
                <span><kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded text-gray-500 font-mono">←</kbd> Still Learning</span>
                <span><kbd class="px-1.5 py-0.5 bg-white border border-gray-200 rounded text-gray-500 font-mono">→</kbd> I Knew It</span>
            </div>
        </div>
    <?php endif; ?>
</div>
