<?php
$quizzes = $quizzes ?? [];
$courses = $courses ?? [];
$isTeacher = ($currentUser['role'] ?? '') === 'teacher';
$selectedCourse = $_GET['course'] ?? '';
$selectedStatus = $_GET['status'] ?? 'all';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Quizzes</h1>
            <p class="mt-1 text-gray-500">Test your knowledge and track your progress</p>
        </div>
        <?php if ($isTeacher): ?>
            <a href="/quizzes/create" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                <i class="fas fa-plus"></i> Create Quiz
            </a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-8">
        <form method="GET" action="/quizzes" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="course" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Course</label>
                <select id="course" name="course" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                    <option value="">All Courses</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= $selectedCourse == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-1">
                <label for="status" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                    <option value="all" <?= $selectedStatus === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="not_taken" <?= $selectedStatus === 'not_taken' ? 'selected' : '' ?>>Not Taken</option>
                    <option value="taken" <?= $selectedStatus === 'taken' ? 'selected' : '' ?>>Taken</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Quiz Grid -->
    <?php if (empty($quizzes)): ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700">No quizzes found</h3>
            <p class="text-gray-500 mt-1">Try adjusting your filters or check back later.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($quizzes as $quiz): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg hover:border-indigo-100 transition-all duration-300 overflow-hidden group">
                    <!-- Colored top bar -->
                    <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    <div class="p-6">
                        <!-- Course badge -->
                        <span class="inline-block px-2.5 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-medium mb-3">
                            <?= htmlspecialchars($quiz['course_name'] ?? 'General') ?>
                        </span>

                        <h3 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-indigo-600 transition-colors">
                            <a href="/quizzes/<?= (int)$quiz['id'] ?>"><?= htmlspecialchars($quiz['title']) ?></a>
                        </h3>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fas fa-question-circle text-indigo-400"></i>
                                <span><?= (int)($quiz['question_count'] ?? 0) ?> questions</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fas fa-clock text-indigo-400"></i>
                                <span><?= (int)($quiz['time_limit'] ?? 0) ?> min</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fas fa-signal text-indigo-400"></i>
                                <span class="capitalize"><?= htmlspecialchars($quiz['difficulty'] ?? 'Medium') ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fas fa-redo text-indigo-400"></i>
                                <span><?= (int)($quiz['attempts_count'] ?? 0) ?> attempts</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
                            <a href="/quizzes/<?= (int)$quiz['id'] ?>" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                                View Details <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                            <?php if ($isTeacher && ($quiz['teacher_id'] ?? 0) == ($currentUser['id'] ?? -1)): ?>
                                <div class="flex items-center gap-2">
                                    <a href="/quizzes/<?= (int)$quiz['id'] ?>/edit" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-500 hover:text-indigo-600 transition-colors" title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <form action="/quizzes/<?= (int)$quiz['id'] ?>" method="POST" onsubmit="return confirm('Delete this quiz?')">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 flex items-center justify-center text-gray-500 hover:text-red-600 transition-colors" title="Delete">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if (!empty($pagination)): ?>
        <div class="mt-8">
            <?php $items = $pagination; include __DIR__ . '/../components/pagination.php'; ?>
        </div>
    <?php endif; ?>
</div>
