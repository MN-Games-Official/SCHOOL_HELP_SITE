<?php
$note = $note ?? null;
$subjects = $subjects ?? [];
$errors = $errors ?? [];
$isEditing = !empty($note);
$colors = [
    '#6366f1' => 'Indigo',
    '#8b5cf6' => 'Purple',
    '#ec4899' => 'Pink',
    '#ef4444' => 'Red',
    '#f97316' => 'Orange',
    '#eab308' => 'Yellow',
    '#22c55e' => 'Green',
    '#06b6d4' => 'Cyan',
    '#3b82f6' => 'Blue',
    '#6b7280' => 'Gray',
];
?>

<div class="max-w-4xl mx-auto">
    <!-- Back Link -->
    <div class="mb-6">
        <a href="<?= $isEditing ? '/notes/' . (int)$note['id'] : '/notes' ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="fas fa-arrow-left"></i> <?= $isEditing ? 'Back to Note' : 'Back to Notes' ?>
        </a>
    </div>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= $isEditing ? 'Edit Note' : 'Create Note' ?></h1>
        <p class="mt-1 text-gray-500"><?= $isEditing ? 'Update your note details' : 'Start a new note' ?></p>
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
    <form method="POST" action="<?= $isEditing ? '/notes/' . (int)$note['id'] . '/edit' : '/notes/create' ?>" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 space-y-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Title</label>
                <input type="text" id="title" name="title" required
                    value="<?= htmlspecialchars($note['title'] ?? '') ?>"
                    placeholder="Enter note title..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
            </div>

            <!-- Subject -->
            <div>
                <label for="subject" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Subject</label>
                <select id="subject" name="subject" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                    <option value="">Select a subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= ($note['subject'] ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Content -->
            <div>
                <label for="content" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Content</label>
                <div class="relative">
                    <div class="absolute left-0 top-0 bottom-0 w-10 bg-gray-50 border-r border-gray-200 rounded-l-xl pointer-events-none flex flex-col pt-3 text-right pr-2 overflow-hidden" aria-hidden="true">
                        <?php for ($i = 1; $i <= 30; $i++): ?>
                            <span class="text-xs text-gray-300 leading-6"><?= $i ?></span>
                        <?php endfor; ?>
                    </div>
                    <textarea id="content" name="content" rows="20" required placeholder="Start writing your note..."
                        class="w-full pl-14 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700 resize-y leading-6 font-mono"><?= htmlspecialchars($note['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Color Picker -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Note Color</label>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($colors as $hex => $name): ?>
                        <label class="relative cursor-pointer" title="<?= $name ?>">
                            <input type="radio" name="color" value="<?= $hex ?>"
                                class="sr-only peer"
                                <?= ($note['color'] ?? '#6366f1') === $hex ? 'checked' : '' ?>>
                            <div class="w-8 h-8 rounded-full border-2 border-transparent peer-checked:border-gray-900 peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-gray-400 transition-all hover:scale-110" style="background-color: <?= $hex ?>"></div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tags -->
            <div>
                <label for="tags" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Tags</label>
                <input type="text" id="tags" name="tags"
                    value="<?= htmlspecialchars($note['tags'] ?? '') ?>"
                    placeholder="e.g., midterm, chapter-5, important (comma separated)"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                <p class="text-xs text-gray-400 mt-1">Separate tags with commas</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                <i class="fas fa-save"></i> <?= $isEditing ? 'Update Note' : 'Save Note' ?>
            </button>
            <a href="<?= $isEditing ? '/notes/' . (int)$note['id'] : '/notes' ?>" class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-center">
                Cancel
            </a>
        </div>
    </form>
</div>
