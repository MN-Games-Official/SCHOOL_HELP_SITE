<?php
$courseTitle = $course['title'] ?? 'Course';
$courseId = $course['id'] ?? 0;
?>

<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="/courses/<?= (int)$courseId ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-4">
            <i class="fas fa-arrow-left"></i> Back to <?= htmlspecialchars($courseTitle) ?>
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create New Lesson</h1>
        <p class="mt-2 text-gray-500">Add a lesson to <span class="font-semibold text-indigo-600"><?= htmlspecialchars($courseTitle) ?></span></p>
    </div>

    <!-- Form -->
    <form action="/courses/<?= (int)$courseId ?>/lessons" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

        <!-- Title -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Lesson Title <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900 placeholder-gray-400"
                   placeholder="Enter lesson title">
            <?php if (!empty($errors['title'])): ?>
                <p class="mt-1.5 text-sm text-red-600"><i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($errors['title']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="content" class="block text-sm font-semibold text-gray-700 mb-2">Lesson Content <span class="text-red-500">*</span></label>
            <textarea id="content" name="content" rows="16" required
                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900 placeholder-gray-400 font-mono text-sm leading-relaxed"
                      placeholder="Write your lesson content here. HTML is supported for rich formatting."><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
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
                           value="<?= htmlspecialchars($old['order_number'] ?? ($nextOrder ?? 1)) ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900">
                    <p class="mt-1.5 text-xs text-gray-400">Position of this lesson in the course.</p>
                </div>
                <div>
                    <label for="duration" class="block text-sm font-semibold text-gray-700 mb-2">Duration (minutes)</label>
                    <input type="number" id="duration" name="duration" min="1"
                           value="<?= htmlspecialchars($old['duration'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900"
                           placeholder="e.g. 30">
                </div>
            </div>
        </div>

        <!-- Video URL -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="video_url" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-video text-indigo-400 mr-1"></i> Video URL <span class="text-gray-400 font-normal">(optional)</span>
            </label>
            <input type="url" id="video_url" name="video_url"
                   value="<?= htmlspecialchars($old['video_url'] ?? '') ?>"
                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900 placeholder-gray-400"
                   placeholder="https://www.youtube.com/embed/...">
            <p class="mt-1.5 text-xs text-gray-400">Paste an embeddable video URL (YouTube, Vimeo, etc.)</p>
        </div>

        <!-- Attachments -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <label for="attachments" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-paperclip text-indigo-400 mr-1"></i> Attachments <span class="text-gray-400 font-normal">(optional)</span>
            </label>
            <div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-xl hover:border-indigo-300 transition-colors">
                <div class="space-y-2 text-center">
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-300"></i>
                    <div class="text-sm text-gray-500">
                        <label for="attachments" class="cursor-pointer font-semibold text-indigo-600 hover:text-indigo-500">
                            Upload files
                        </label>
                        <span> or drag and drop</span>
                    </div>
                    <p class="text-xs text-gray-400">PDF, DOC, PPT, images up to 10MB each</p>
                    <input id="attachments" name="attachments[]" type="file" multiple class="sr-only">
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-4">
            <a href="/courses/<?= (int)$courseId ?>" class="px-6 py-3 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                <i class="fas fa-plus"></i> Create Lesson
            </button>
        </div>
    </form>
</div>
