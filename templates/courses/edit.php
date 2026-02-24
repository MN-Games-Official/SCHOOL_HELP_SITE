<?php
/**
 * Edit Course Form Template
 *
 * Expected variables (via extract):
 *   $user, $course, $error
 */

$config    = require BASE_PATH . '/config/app.php';
$subjects  = $config['subjects'] ?? [];
$csrfToken = $_SESSION['_csrf_token'] ?? '';

$courseData    = $course ?? [];
$courseId      = $courseData['id'] ?? '';
$courseTitle   = $courseData['title'] ?? '';
$description  = $courseData['description'] ?? '';
$courseSubject = $courseData['subject'] ?? '';
$difficulty   = $courseData['difficulty'] ?? '';
$bannerColor  = $courseData['banner_color'] ?? 'bg-indigo-500';
$syllabus     = $courseData['syllabus'] ?? '';
$prerequisites = $courseData['prerequisites'] ?? '';
$status       = $courseData['status'] ?? 'active';
?>

<div class="mx-auto max-w-3xl">
    <!-- Header -->
    <div class="mb-8">
        <nav class="mb-4 text-sm">
            <ol class="flex items-center gap-2 text-gray-500">
                <li><a href="/courses" class="hover:text-indigo-600 transition">Courses</a></li>
                <li><i class="fas fa-chevron-right text-[10px] text-gray-400"></i></li>
                <li><a href="/courses/<?= htmlspecialchars($courseId) ?>" class="hover:text-indigo-600 transition"><?= htmlspecialchars($courseTitle) ?></a></li>
                <li><i class="fas fa-chevron-right text-[10px] text-gray-400"></i></li>
                <li class="font-medium text-gray-900">Edit</li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Edit Course</h1>
        <p class="mt-1 text-sm text-gray-500">Update course details below.</p>
    </div>

    <!-- Validation Error -->
    <?php if (!empty($error)): ?>
        <div class="mb-6 flex items-center gap-3 rounded-xl bg-red-50 p-4 text-sm text-red-700 ring-1 ring-red-200">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="/courses/<?= htmlspecialchars($courseId) ?>/update" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <!-- Title -->
        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-gray-700">Course Title <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" required minlength="3" maxlength="200"
                   value="<?= htmlspecialchars($courseTitle) ?>"
                   class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
            <textarea id="description" name="description" rows="4" required minlength="10" maxlength="5000"
                      class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"><?= htmlspecialchars($description) ?></textarea>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Subject -->
            <div>
                <label for="subject" class="mb-1.5 block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
                <select id="subject" name="subject" required
                        class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                    <option value="">Select a subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= $courseSubject === $s ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Difficulty -->
            <div>
                <label for="difficulty" class="mb-1.5 block text-sm font-medium text-gray-700">Difficulty Level</label>
                <select id="difficulty" name="difficulty"
                        class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                    <option value="">Select level</option>
                    <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= $difficulty === $lvl ? 'selected' : '' ?>>
                            <?= $lvl ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Banner Color -->
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700">Banner Color</label>
            <div class="flex flex-wrap gap-3">
                <?php
                $colorOptions = [
                    'bg-indigo-500' => '#6366f1',
                    'bg-purple-500' => '#a855f7',
                    'bg-blue-500'   => '#3b82f6',
                    'bg-emerald-500'=> '#10b981',
                    'bg-rose-500'   => '#f43f5e',
                    'bg-amber-500'  => '#f59e0b',
                    'bg-cyan-500'   => '#06b6d4',
                    'bg-pink-500'   => '#ec4899',
                ];
                foreach ($colorOptions as $twClass => $hex): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="banner_color" value="<?= $twClass ?>" class="peer sr-only" <?= $bannerColor === $twClass ? 'checked' : '' ?>>
                        <div class="h-10 w-10 rounded-lg <?= $twClass ?> ring-2 ring-transparent peer-checked:ring-gray-900 peer-checked:ring-offset-2 transition hover:scale-110"></div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Syllabus -->
        <div>
            <label for="syllabus" class="mb-1.5 block text-sm font-medium text-gray-700">Syllabus</label>
            <textarea id="syllabus" name="syllabus" rows="4"
                      placeholder="Outline the course syllabus..."
                      class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"><?= htmlspecialchars($syllabus) ?></textarea>
        </div>

        <!-- Prerequisites -->
        <div>
            <label for="prerequisites" class="mb-1.5 block text-sm font-medium text-gray-700">Prerequisites</label>
            <textarea id="prerequisites" name="prerequisites" rows="3"
                      placeholder="List any prerequisites..."
                      class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"><?= htmlspecialchars($prerequisites) ?></textarea>
        </div>

        <!-- Status -->
        <div>
            <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
            <select id="status" name="status"
                    class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>

        <!-- Submit / Delete -->
        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
            <!-- Delete -->
            <button type="button" onclick="document.getElementById('delete-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition">
                <i class="fas fa-trash-alt"></i> Delete Course
            </button>

            <div class="flex items-center gap-3">
                <a href="/courses/<?= htmlspecialchars($courseId) ?>" class="rounded-xl px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 transition">Cancel</a>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-200 transition">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="document.getElementById('delete-modal').classList.add('hidden')"></div>
    <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
            <h3 class="text-center text-lg font-bold text-gray-900">Delete Course</h3>
            <p class="mt-2 text-center text-sm text-gray-500">
                Are you sure you want to delete <strong>&ldquo;<?= htmlspecialchars($courseTitle) ?>&rdquo;</strong>?
                This action cannot be undone and will remove all associated lessons, assignments and enrollments.
            </p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <button type="button" onclick="document.getElementById('delete-modal').classList.add('hidden')"
                        class="rounded-xl px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                    Cancel
                </button>
                <form method="POST" action="/courses/<?= htmlspecialchars($courseId) ?>/delete" class="inline">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-red-700 transition">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
