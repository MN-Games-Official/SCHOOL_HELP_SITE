<?php
$courseTitle = $course['title'] ?? 'Course';
$courseId = $course['id'] ?? 0;
$lessonId = $lesson['id'] ?? 0;
$lessonTitle = $lesson['title'] ?? '';
?>

<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="/lessons/<?= (int)$lessonId ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-4">
            <i class="fas fa-arrow-left"></i> Back to Lesson
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Lesson</h1>
        <p class="mt-2 text-gray-500">Editing <span class="font-semibold text-indigo-600"><?= htmlspecialchars($lessonTitle) ?></span></p>
    </div>

    <!-- Form -->
    <form action="/lessons/<?= (int)$lessonId ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
        <input type="hidden" name="_method" value="PUT">

        <!-- Title -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Lesson Title <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($old['title'] ?? $lesson['title'] ?? '') ?>"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
            <?php if (!empty($errors['title'])): ?>
                <p class="mt-1.5 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['title']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">Lesson Content <span class="text-red-500">*</span></label>
            <textarea id="content" name="content" rows="16" required
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900 font-mono text-sm leading-relaxed"><?= htmlspecialchars($old['content'] ?? $lesson['content'] ?? '') ?></textarea>
            <p class="mt-1.5 text-xs text-gray-400">Supports HTML formatting for rich text content.</p>
            <?php if (!empty($errors['content'])): ?>
                <p class="mt-1.5 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['content']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Order & Duration -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="order_number" class="block text-sm font-semibold text-gray-700 mb-2">Order Number</label>
                    <input type="number" id="order_number" name="order_number" min="1"
                           value="<?= htmlspecialchars($old['order_number'] ?? $lesson['order_number'] ?? 1) ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
                </div>
                <div>
                    <label for="duration" class="block text-sm font-semibold text-gray-700 mb-2">Duration (minutes)</label>
                    <input type="number" id="duration" name="duration" min="1"
                           value="<?= htmlspecialchars($old['duration'] ?? $lesson['duration'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
                </div>
            </div>
        </div>

        <!-- Video URL -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="video_url" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-video text-indigo-400 mr-1"></i> Video URL <span class="text-gray-400 font-normal">(optional)</span>
            </label>
            <input type="url" id="video_url" name="video_url"
                   value="<?= htmlspecialchars($old['video_url'] ?? $lesson['video_url'] ?? '') ?>"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900 placeholder-gray-400"
                   placeholder="https://www.youtube.com/embed/...">
        </div>

        <!-- Attachments -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="attachments" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-paperclip text-indigo-400 mr-1"></i> Attachments <span class="text-gray-400 font-normal">(optional)</span>
            </label>
            <?php if (!empty($lesson['attachments'])): ?>
                <div class="mb-4 space-y-2">
                    <?php foreach ($lesson['attachments'] as $att): ?>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <i class="fas fa-file text-gray-400"></i>
                            <span class="text-sm text-gray-700 flex-1"><?= htmlspecialchars($att['name'] ?? $att['filename'] ?? 'File') ?></span>
                            <a href="<?= htmlspecialchars($att['url'] ?? '#') ?>" class="text-xs text-indigo-600 hover:underline">Download</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-xl hover:border-indigo-300 transition-colors">
                <div class="space-y-2 text-center">
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-300"></i>
                    <div class="text-sm text-gray-500">
                        <label for="attachments" class="cursor-pointer font-semibold text-indigo-600 hover:text-indigo-500">Upload new files</label>
                    </div>
                    <p class="text-xs text-gray-400">PDF, DOC, PPT, images up to 10MB each</p>
                    <input id="attachments" name="attachments[]" type="file" multiple class="sr-only">
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <!-- Delete -->
            <button type="button" onclick="document.getElementById('delete-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-5 py-3 text-red-600 font-semibold rounded-xl hover:bg-red-50 transition-colors">
                <i class="fas fa-trash-alt"></i> Delete Lesson
            </button>
            <div class="flex items-center gap-4">
                <a href="/lessons/<?= (int)$lessonId ?>" class="px-6 py-3 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors">Cancel</a>
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                    <i class="fas fa-save"></i> Update Lesson
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
            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Lesson</h3>
            <p class="text-gray-500 text-sm">Are you sure you want to delete <strong><?= htmlspecialchars($lessonTitle) ?></strong>? This action cannot be undone.</p>
        </div>
        <div class="mt-6 flex items-center justify-center gap-3">
            <button onclick="document.getElementById('delete-modal').classList.add('hidden')"
                    class="px-5 py-2.5 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors">Cancel</button>
            <form action="/lessons/<?= (int)$lessonId ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
