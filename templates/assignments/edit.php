<?php
$courses = $courses ?? [];
$assignment = $assignment ?? [];
$assignmentId = $assignment['id'] ?? 0;
$assignmentTitle = $assignment['title'] ?? '';
?>

<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="/assignments/<?= (int)$assignmentId ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-4">
            <i class="fas fa-arrow-left"></i> Back to Assignment
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Assignment</h1>
        <p class="mt-2 text-gray-500">Editing <span class="font-semibold text-indigo-600"><?= htmlspecialchars($assignmentTitle) ?></span></p>
    </div>

    <!-- Form -->
    <form action="/assignments/<?= (int)$assignmentId ?>" method="POST" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
        <input type="hidden" name="_method" value="PUT">

        <!-- Title -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($old['title'] ?? $assignment['title'] ?? '') ?>"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
            <?php if (!empty($errors['title'])): ?>
                <p class="mt-1.5 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['title']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Description & Instructions -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900"><?= htmlspecialchars($old['description'] ?? $assignment['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label for="instructions" class="block text-sm font-semibold text-gray-700 mb-2">Instructions</label>
                <textarea id="instructions" name="instructions" rows="6"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900 font-mono text-sm"><?= htmlspecialchars($old['instructions'] ?? $assignment['instructions'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Course Selector -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="course_id" class="block text-sm font-semibold text-gray-700 mb-2">Course <span class="text-red-500">*</span></label>
            <select id="course_id" name="course_id" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                <option value="">Select a course</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ($old['course_id'] ?? $assignment['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Due Date & Score -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="due_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-indigo-400 mr-1"></i> Due Date <span class="text-red-500">*</span>
                    </label>
                    <?php
                    $dueDateValue = $old['due_date'] ?? '';
                    if (empty($dueDateValue) && !empty($assignment['due_date'])) {
                        $dueDateValue = date('Y-m-d\TH:i', strtotime($assignment['due_date']));
                    }
                    ?>
                    <input type="datetime-local" id="due_date" name="due_date" required
                           value="<?= htmlspecialchars($dueDateValue) ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
                </div>
                <div>
                    <label for="max_score" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-star text-indigo-400 mr-1"></i> Max Score
                    </label>
                    <input type="number" id="max_score" name="max_score" min="1"
                           value="<?= htmlspecialchars($old['max_score'] ?? $assignment['max_score'] ?? '100') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
                </div>
            </div>
        </div>

        <!-- File Upload Toggle -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="allow_file_upload" value="1"
                       <?= !empty($old['allow_file_upload'] ?? $assignment['allow_file_upload'] ?? false) ? 'checked' : '' ?>
                       class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700">Allow File Upload</span>
                    <p class="text-xs text-gray-400">Students can upload files along with their text submission.</p>
                </div>
            </label>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <!-- Delete -->
            <button type="button" onclick="document.getElementById('delete-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-5 py-3 text-red-600 font-semibold rounded-xl hover:bg-red-50 transition-colors">
                <i class="fas fa-trash-alt"></i> Delete
            </button>
            <div class="flex items-center gap-4">
                <a href="/assignments/<?= (int)$assignmentId ?>" class="px-6 py-3 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors">Cancel</a>
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                    <i class="fas fa-save"></i> Update Assignment
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50" onclick="document.getElementById('delete-modal').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <div class="text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Assignment</h3>
            <p class="text-gray-500 text-sm">Are you sure you want to delete <strong><?= htmlspecialchars($assignmentTitle) ?></strong>? All submissions will also be deleted. This cannot be undone.</p>
        </div>
        <div class="mt-6 flex items-center justify-center gap-3">
            <button onclick="document.getElementById('delete-modal').classList.add('hidden')"
                    class="px-5 py-2.5 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors">Cancel</button>
            <form action="/assignments/<?= (int)$assignmentId ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
