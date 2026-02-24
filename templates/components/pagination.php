<?php
$currentPage = $currentPage ?? 1;
$totalPages  = $totalPages  ?? 1;
$baseUrl     = $baseUrl     ?? '?';

if ($totalPages <= 1) return;

$separator = (strpos($baseUrl, '?') !== false) ? '&' : '?';

// Calculate visible page range
$rangeStart = max(1, $currentPage - 2);
$rangeEnd   = min($totalPages, $currentPage + 2);

if ($rangeEnd - $rangeStart < 4) {
    if ($rangeStart === 1) {
        $rangeEnd = min($totalPages, $rangeStart + 4);
    } else {
        $rangeStart = max(1, $rangeEnd - 4);
    }
}
?>
<nav class="flex items-center justify-between" aria-label="Pagination">
    <!-- Mobile pagination -->
    <div class="flex flex-1 justify-between sm:hidden">
        <?php if ($currentPage > 1): ?>
            <a href="<?= htmlspecialchars($baseUrl . $separator . 'page=' . ($currentPage - 1)) ?>"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Previous
            </a>
        <?php else: ?>
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                Previous
            </span>
        <?php endif; ?>

        <?php if ($currentPage < $totalPages): ?>
            <a href="<?= htmlspecialchars($baseUrl . $separator . 'page=' . ($currentPage + 1)) ?>"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Next
            </a>
        <?php else: ?>
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-gray-50 border border-gray-200 rounded-lg cursor-not-allowed">
                Next
            </span>
        <?php endif; ?>
    </div>

    <!-- Desktop pagination -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <p class="text-sm text-gray-500">
            Page <span class="font-medium text-gray-700"><?= $currentPage ?></span> of
            <span class="font-medium text-gray-700"><?= $totalPages ?></span>
        </p>

        <div class="flex items-center gap-1">
            <!-- Previous -->
            <?php if ($currentPage > 1): ?>
                <a href="<?= htmlspecialchars($baseUrl . $separator . 'page=' . ($currentPage - 1)) ?>"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <i class="fas fa-chevron-left text-xs"></i>
                </a>
            <?php else: ?>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 cursor-not-allowed">
                    <i class="fas fa-chevron-left text-xs"></i>
                </span>
            <?php endif; ?>

            <!-- First page + ellipsis -->
            <?php if ($rangeStart > 1): ?>
                <a href="<?= htmlspecialchars($baseUrl . $separator . 'page=1') ?>"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">1</a>
                <?php if ($rangeStart > 2): ?>
                    <span class="inline-flex items-center justify-center w-9 h-9 text-gray-400 text-sm">&hellip;</span>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Page numbers -->
            <?php for ($i = $rangeStart; $i <= $rangeEnd; $i++): ?>
                <?php if ($i === $currentPage): ?>
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm font-semibold bg-indigo-600 text-white shadow-sm">
                        <?= $i ?>
                    </span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($baseUrl . $separator . 'page=' . $i) ?>"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Last page + ellipsis -->
            <?php if ($rangeEnd < $totalPages): ?>
                <?php if ($rangeEnd < $totalPages - 1): ?>
                    <span class="inline-flex items-center justify-center w-9 h-9 text-gray-400 text-sm">&hellip;</span>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($baseUrl . $separator . 'page=' . $totalPages) ?>"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                    <?= $totalPages ?>
                </a>
            <?php endif; ?>

            <!-- Next -->
            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= htmlspecialchars($baseUrl . $separator . 'page=' . ($currentPage + 1)) ?>"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <i class="fas fa-chevron-right text-xs"></i>
                </a>
            <?php else: ?>
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-gray-300 cursor-not-allowed">
                    <i class="fas fa-chevron-right text-xs"></i>
                </span>
            <?php endif; ?>
        </div>
    </div>
</nav>
