<?php
$modalId      = $modalId      ?? 'modal';
$modalTitle   = $modalTitle   ?? '';
$modalContent = $modalContent ?? '';
?>
<div id="<?= htmlspecialchars($modalId) ?>"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="<?= htmlspecialchars($modalId) ?>-title">

    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
         onclick="closeModal('<?= htmlspecialchars($modalId) ?>')"></div>

    <!-- Modal panel -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl transform transition-all">

            <!-- Header -->
            <?php if ($modalTitle): ?>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 id="<?= htmlspecialchars($modalId) ?>-title" class="text-lg font-semibold text-gray-900">
                    <?= htmlspecialchars($modalTitle) ?>
                </h3>
                <button onclick="closeModal('<?= htmlspecialchars($modalId) ?>')"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                        aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php else: ?>
            <button onclick="closeModal('<?= htmlspecialchars($modalId) ?>')"
                    class="absolute top-3 right-3 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors z-10"
                    aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            <?php endif; ?>

            <!-- Body -->
            <div class="px-6 py-5">
                <?= $modalContent ?>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    var modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    var modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(function(m) {
            closeModal(m.id);
        });
    }
});
</script>
