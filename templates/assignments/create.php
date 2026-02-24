<?php
$courses = $courses ?? [];
?>

<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="/assignments" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-4">
            <i class="fas fa-arrow-left"></i> Back to Assignments
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Assignment</h1>
        <p class="mt-2 text-gray-500">Create a new assignment for your students</p>
    </div>

    <!-- Form -->
    <form action="/assignments" method="POST" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

        <!-- Title -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900"
                   placeholder="Assignment title">
            <?php if (!empty($errors['title'])): ?>
                <p class="mt-1.5 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['title']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Description & Instructions -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900"
                          placeholder="Brief description of the assignment"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
            </div>
            <div>
                <label for="instructions" class="block text-sm font-semibold text-gray-700 mb-2">Instructions</label>
                <textarea id="instructions" name="instructions" rows="6"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900 font-mono text-sm"
                          placeholder="Detailed instructions for students. HTML is supported."><?= htmlspecialchars($old['instructions'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Course Selector -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="course_id" class="block text-sm font-semibold text-gray-700 mb-2">Course <span class="text-red-500">*</span></label>
            <select id="course_id" name="course_id" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                <option value="">Select a course</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= ($old['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['course_id'])): ?>
                <p class="mt-1.5 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['course_id']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Due Date & Score -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="due_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-indigo-400 mr-1"></i> Due Date <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" id="due_date" name="due_date" required
                           value="<?= htmlspecialchars($old['due_date'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
                    <?php if (!empty($errors['due_date'])): ?>
                        <p class="mt-1.5 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['due_date']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="max_score" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-star text-indigo-400 mr-1"></i> Max Score
                    </label>
                    <input type="number" id="max_score" name="max_score" min="1"
                           value="<?= htmlspecialchars($old['max_score'] ?? '100') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
                </div>
            </div>
        </div>

        <!-- File Upload Toggle -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="allow_file_upload" value="1"
                       <?= !empty($old['allow_file_upload']) ? 'checked' : '' ?>
                       class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                <div>
                    <span class="text-sm font-semibold text-gray-700">Allow File Upload</span>
                    <p class="text-xs text-gray-400">Students can upload files along with their text submission.</p>
                </div>
            </label>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-4">
            <a href="/assignments" class="px-6 py-3 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                <i class="fas fa-plus"></i> Create Assignment
            </button>
        </div>
    </form>
</div>
