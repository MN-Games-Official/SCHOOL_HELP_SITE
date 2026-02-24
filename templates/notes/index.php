<?php
$notes = $notes ?? [];
$subjects = $subjects ?? [];
$selectedSubject = $_GET['subject'] ?? '';
$selectedSort = $_GET['sort'] ?? 'newest';
$searchQuery = $_GET['q'] ?? '';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Notes</h1>
            <p class="mt-1 text-gray-500">Organize your study notes in one place</p>
        </div>
        <a href="/notes/create" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
            <i class="fas fa-plus"></i> Create Note
        </a>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-8">
        <form method="GET" action="/notes" class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <label for="q" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" id="q" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search notes..."
                        class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                </div>
            </div>
            <!-- Subject Filter -->
            <div class="sm:w-48">
                <label for="subject" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Subject</label>
                <select id="subject" name="subject" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= $selectedSubject === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Sort -->
            <div class="sm:w-44">
                <label for="sort" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Sort By</label>
                <select id="sort" name="sort" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                    <option value="newest" <?= $selectedSort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= $selectedSort === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="alpha" <?= $selectedSort === 'alpha' ? 'selected' : '' ?>>Alphabetical</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Subject Tags (Quick Filter) -->
    <?php if (!empty($subjects)): ?>
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="/notes" class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors <?= empty($selectedSubject) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">All</a>
            <?php foreach ($subjects as $s): ?>
                <a href="/notes?subject=<?= urlencode($s) ?>&sort=<?= urlencode($selectedSort) ?>"
                   class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors <?= $selectedSubject === $s ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                    <?= htmlspecialchars($s) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Notes Grid -->
    <?php if (empty($notes)): ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <i class="fas fa-sticky-note text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700">No notes yet — start taking notes!</h3>
            <p class="text-gray-500 mt-1 mb-6">Create your first note to get started.</p>
            <a href="/notes/create" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus"></i> Create Note
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($notes as $note): ?>
                <?php
                $noteColor = $note['color'] ?? '#6366f1';
                $preview = mb_substr(strip_tags($note['content'] ?? ''), 0, 100);
                if (mb_strlen(strip_tags($note['content'] ?? '')) > 100) $preview .= '...';
                ?>
                <a href="/notes/<?= (int)$note['id'] ?>" class="group block bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-100 transition-all duration-200 overflow-hidden">
                    <!-- Color Indicator -->
                    <div class="h-1.5 w-full" style="background-color: <?= htmlspecialchars($noteColor) ?>"></div>
                    <div class="p-5">
                        <h3 class="text-base font-bold text-gray-900 group-hover:text-indigo-600 transition-colors mb-2 line-clamp-1"><?= htmlspecialchars($note['title'] ?? 'Untitled') ?></h3>
                        <p class="text-sm text-gray-500 mb-3 line-clamp-3"><?= htmlspecialchars($preview) ?></p>
                        <div class="flex items-center justify-between">
                            <?php if (!empty($note['subject'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 bg-indigo-50 text-indigo-700 rounded-full text-xs font-semibold"><?= htmlspecialchars($note['subject']) ?></span>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            <span class="text-xs text-gray-400">
                                <i class="fas fa-clock mr-1"></i>
                                <?= htmlspecialchars(!empty($note['updated_at']) ? date('M j, Y', strtotime($note['updated_at'])) : '') ?>
                            </span>
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

    <!-- Floating Action Button (Mobile) -->
    <a href="/notes/create" class="sm:hidden fixed bottom-6 right-6 w-14 h-14 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition-colors flex items-center justify-center text-xl z-50" aria-label="Create Note">
        <i class="fas fa-plus"></i>
    </a>
</div>
