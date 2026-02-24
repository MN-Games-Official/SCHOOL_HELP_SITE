<?php
$sets = $sets ?? [];
$subjects = $subjects ?? [];
$selectedFilter = $_GET['filter'] ?? 'my';
$searchQuery = $_GET['q'] ?? '';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Flashcard Sets</h1>
            <p class="mt-1 text-gray-500">Create and study flashcard sets to boost your learning</p>
        </div>
        <a href="/flashcards/create" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
            <i class="fas fa-plus"></i> Create Set
        </a>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-8">
        <form method="GET" action="/flashcards" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="q" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="q" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search flashcard sets..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                </div>
            </div>
            <div class="sm:w-44">
                <label for="filter" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Filter</label>
                <select id="filter" name="filter" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                    <option value="my" <?= $selectedFilter === 'my' ? 'selected' : '' ?>>My Sets</option>
                    <option value="public" <?= $selectedFilter === 'public' ? 'selected' : '' ?>>Public Sets</option>
                    <option value="all" <?= $selectedFilter === 'all' ? 'selected' : '' ?>>All Sets</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Flashcard Sets Grid -->
    <?php if (empty($sets)): ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <i class="fas fa-layer-group text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700">No flashcard sets found</h3>
            <p class="text-gray-500 mt-1 mb-6">Create your first set to start studying!</p>
            <a href="/flashcards/create" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus"></i> Create Set
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($sets as $set): ?>
                <?php
                $mastery = $set['mastery'] ?? 0;
                $masteryColor = $mastery >= 80 ? 'bg-green-500' : ($mastery >= 50 ? 'bg-yellow-500' : 'bg-red-500');
                ?>
                <a href="/flashcards/<?= (int)$set['id'] ?>" class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-100 transition-all duration-200 overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-layer-group text-indigo-600"></i>
                            </div>
                            <?php if (!empty($set['is_public'])): ?>
                                <span class="inline-flex items-center px-2 py-0.5 bg-green-50 text-green-600 rounded-full text-xs font-semibold">
                                    <i class="fas fa-globe text-xs mr-1"></i> Public
                                </span>
                            <?php endif; ?>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 group-hover:text-indigo-600 transition-colors mb-1 line-clamp-1"><?= htmlspecialchars($set['title'] ?? 'Untitled Set') ?></h3>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 mb-3">
                            <span><i class="fas fa-clone mr-1 text-gray-400"></i><?= (int)($set['card_count'] ?? 0) ?> cards</span>
                            <?php if (!empty($set['subject'])): ?>
                                <span><i class="fas fa-tag mr-1 text-gray-400"></i><?= htmlspecialchars($set['subject']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($set['last_studied'])): ?>
                                <span><i class="fas fa-clock mr-1 text-gray-400"></i><?= htmlspecialchars(date('M j', strtotime($set['last_studied']))) ?></span>
                            <?php endif; ?>
                        </div>
                        <!-- Mastery Bar -->
                        <div>
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-500 font-medium">Mastery</span>
                                <span class="font-bold text-gray-700"><?= (int)$mastery ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                <div class="<?= $masteryColor ?> h-1.5 rounded-full transition-all duration-500" style="width: <?= (int)$mastery ?>%"></div>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if (!empty($pagination)): ?>
        <div class="mt-8">
            <?php $items = $pagination; include __DIR__ . '/../components/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>
