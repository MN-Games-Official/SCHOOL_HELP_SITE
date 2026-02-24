<?php
$note = $note ?? [];
$noteColor = $note['color'] ?? '#6366f1';
?>

<div class="max-w-4xl mx-auto">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="/notes" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="fas fa-arrow-left"></i> Back to Notes
        </a>
    </div>

    <!-- Note Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Color Bar -->
        <div class="h-2 w-full" style="background-color: <?= htmlspecialchars($noteColor) ?>"></div>

        <div class="p-6 sm:p-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($note['title'] ?? 'Untitled Note') ?></h1>
                    <div class="flex flex-wrap items-center gap-3">
                        <?php if (!empty($note['subject'])): ?>
                            <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-sm font-semibold">
                                <i class="fas fa-tag mr-1.5 text-xs"></i> <?= htmlspecialchars($note['subject']) ?>
                            </span>
                        <?php endif; ?>
                        <span class="text-sm text-gray-400">
                            <i class="fas fa-clock mr-1"></i>
                            Last modified: <?= htmlspecialchars(!empty($note['updated_at']) ? date('M j, Y \a\t g:i A', strtotime($note['updated_at'])) : 'Unknown') ?>
                        </span>
                    </div>
                </div>
                <!-- Action Buttons -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a href="/notes/<?= (int)$note['id'] ?>/edit" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-sm">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <form method="POST" action="/notes/<?= (int)$note['id'] ?>/delete" class="inline" onsubmit="return confirm('Are you sure you want to delete this note?')">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 font-semibold rounded-xl hover:bg-red-100 transition-colors text-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                    <button id="share-note-btn" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm">
                        <i class="fas fa-share-nodes"></i> Share
                    </button>
                </div>
            </div>

            <!-- Divider -->
            <hr class="border-gray-100 mb-6">

            <!-- Note Content -->
            <div class="prose prose-sm sm:prose max-w-none text-gray-700 leading-relaxed">
                <?= $note['content_html'] ?? nl2br(htmlspecialchars($note['content'] ?? '')) ?>
            </div>
        </div>
    </div>
</div>
