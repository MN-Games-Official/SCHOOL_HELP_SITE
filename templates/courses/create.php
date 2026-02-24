<?php
/**
 * Create Course Form Template
 *
 * Expected variables (via extract):
 *   $user, $error
 */

$config   = require BASE_PATH . '/config/app.php';
$subjects = $config['subjects'] ?? [];
$csrfToken = $_SESSION['_csrf_token'] ?? '';
$oldInput  = $_SESSION['_old_input'] ?? [];
unset($_SESSION['_old_input']);
?>

<div class="mx-auto max-w-3xl">
    <!-- Header -->
    <div class="mb-8">
        <nav class="mb-4 text-sm">
            <ol class="flex items-center gap-2 text-gray-500">
                <li><a href="/courses" class="hover:text-indigo-600 transition">Courses</a></li>
                <li><i class="fas fa-chevron-right text-[10px] text-gray-400"></i></li>
                <li class="font-medium text-gray-900">Create Course</li>
            </ol>
        </nav>
        <h1 class="text-2xl font-bold text-gray-900">Create a New Course</h1>
        <p class="mt-1 text-sm text-gray-500">Fill in the details below to create your course.</p>
    </div>

    <!-- Validation Error -->
    <?php if (!empty($error)): ?>
        <div class="mb-6 flex items-center gap-3 rounded-xl bg-red-50 p-4 text-sm text-red-700 ring-1 ring-red-200">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="POST" action="/courses" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <!-- Title -->
        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-gray-700">Course Title <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" required minlength="3" maxlength="200"
                   value="<?= htmlspecialchars($oldInput['title'] ?? '') ?>"
                   placeholder="e.g. Introduction to Python Programming"
                   class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700">Description <span class="text-red-500">*</span></label>
            <textarea id="description" name="description" rows="4" required minlength="10" maxlength="5000"
                      placeholder="Describe what students will learn in this course..."
                      class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"><?= htmlspecialchars($oldInput['description'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <!-- Subject -->
            <div>
                <label for="subject" class="mb-1.5 block text-sm font-medium text-gray-700">Subject <span class="text-red-500">*</span></label>
                <select id="subject" name="subject" required
                        class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
                    <option value="">Select a subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= ($oldInput['subject'] ?? '') === $s ? 'selected' : '' ?>>
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
                        <option value="<?= $lvl ?>" <?= ($oldInput['difficulty'] ?? '') === $lvl ? 'selected' : '' ?>>
                            <?= $lvl ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Banner Color -->
        <div>
            <label for="banner_color" class="mb-1.5 block text-sm font-medium text-gray-700">Banner Color</label>
            <div class="flex flex-wrap gap-3" id="color-picker">
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
                $selectedColor = $oldInput['banner_color'] ?? 'bg-indigo-500';
                foreach ($colorOptions as $twClass => $hex): ?>
                    <label class="cursor-pointer">
                        <input type="radio" name="banner_color" value="<?= $twClass ?>" class="peer sr-only" <?= $selectedColor === $twClass ? 'checked' : '' ?>>
                        <div class="h-10 w-10 rounded-lg <?= $twClass ?> ring-2 ring-transparent peer-checked:ring-gray-900 peer-checked:ring-offset-2 transition hover:scale-110"></div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Syllabus -->
        <div>
            <label for="syllabus" class="mb-1.5 block text-sm font-medium text-gray-700">Syllabus</label>
            <textarea id="syllabus" name="syllabus" rows="4"
                      placeholder="Outline the course syllabus, week by week..."
                      class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"><?= htmlspecialchars($oldInput['syllabus'] ?? '') ?></textarea>
        </div>

        <!-- Prerequisites -->
        <div>
            <label for="prerequisites" class="mb-1.5 block text-sm font-medium text-gray-700">Prerequisites</label>
            <textarea id="prerequisites" name="prerequisites" rows="3"
                      placeholder="List any prerequisites students should have..."
                      class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition"><?= htmlspecialchars($oldInput['prerequisites'] ?? '') ?></textarea>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <a href="/courses" class="rounded-xl px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 transition">Cancel</a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-200 transition">
                <i class="fas fa-plus-circle"></i> Create Course
            </button>
        </div>
    </form>
</div>
