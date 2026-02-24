<?php
$items = $items ?? [];
if (empty($items)) return;
$lastIndex = count($items) - 1;
?>
<nav class="flex items-center text-sm" aria-label="Breadcrumb">
    <ol class="flex items-center gap-1.5 flex-wrap">
        <!-- Home -->
        <li class="flex items-center">
            <a href="/dashboard" class="text-gray-400 hover:text-indigo-600 transition-colors">
                <i class="fas fa-home text-xs"></i>
            </a>
        </li>

        <?php foreach ($items as $index => $item):
            $isLast = ($index === $lastIndex);
            $label  = $item['label'] ?? $item[0] ?? '';
            $url    = $item['url']   ?? $item[1] ?? '';
        ?>
            <li class="flex items-center gap-1.5">
                <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                <?php if ($isLast || empty($url)): ?>
                    <span class="font-medium text-gray-700"><?= htmlspecialchars($label) ?></span>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($url) ?>" class="text-gray-500 hover:text-indigo-600 transition-colors">
                        <?= htmlspecialchars($label) ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
