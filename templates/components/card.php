<?php
$cardClass  = $cardClass  ?? '';
$cardHeader = $cardHeader ?? '';
$cardBody   = $cardBody   ?? '';
$cardFooter = $cardFooter ?? '';
?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden <?= htmlspecialchars($cardClass) ?>">
    <?php if ($cardHeader): ?>
    <div class="px-6 py-4 border-b border-gray-100">
        <?= $cardHeader ?>
    </div>
    <?php endif; ?>

    <div class="px-6 py-5">
        <?= $cardBody ?>
    </div>

    <?php if ($cardFooter): ?>
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
        <?= $cardFooter ?>
    </div>
    <?php endif; ?>
</div>
