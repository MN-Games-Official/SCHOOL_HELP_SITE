<?php
$assignments = $assignments ?? [];
$courses = $courses ?? [];
$isTeacher = ($currentUser['role'] ?? '') === 'teacher';
$isStudent = ($currentUser['role'] ?? '') === 'student';
$selectedCourse = $_GET['course'] ?? '';
$selectedStatus = $_GET['status'] ?? 'all';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Assignments</h1>
            <p class="mt-1 text-gray-500">Manage and track your assignments</p>
        </div>
        <?php if ($isTeacher): ?>
            <a href="/assignments/create" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                <i class="fas fa-plus"></i> Create Assignment
            </a>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-8">
        <form method="GET" action="/assignments" class="flex flex-col sm:flex-row gap-4">
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
                    <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="submitted" <?= $selectedStatus === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                    <option value="graded" <?= $selectedStatus === 'graded' ? 'selected' : '' ?>>Graded</option>
                    <option value="overdue" <?= $selectedStatus === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors text-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Assignments List -->
    <?php if (empty($assignments)): ?>
        <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
            <i class="fas fa-clipboard-check text-5xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-700">No assignments found</h3>
            <p class="text-gray-500 mt-1">Try adjusting your filters or check back later.</p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($assignments as $assignment): ?>
                <?php
                $status = $assignment['status'] ?? 'pending';
                $statusConfig = [
                    'pending'   => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-clock', 'label' => 'Pending'],
                    'submitted' => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'icon' => 'fa-paper-plane', 'label' => 'Submitted'],
                    'graded'    => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'icon' => 'fa-check-circle', 'label' => 'Graded'],
                    'overdue'   => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'icon' => 'fa-exclamation-circle', 'label' => 'Overdue'],
                ];
                $sc = $statusConfig[$status] ?? $statusConfig['pending'];
                $dueDate = $assignment['due_date'] ?? '';
                $isOverdue = !empty($dueDate) && strtotime($dueDate) < time() && $status === 'pending';
                if ($isOverdue) $sc = $statusConfig['overdue'];
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-100 transition-all duration-200 overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-5 sm:p-6">
                        <!-- Icon -->
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                            <i class="fas fa-file-alt text-indigo-600 text-lg"></i>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h3 class="text-lg font-bold text-gray-900">
                                    <a href="/assignments/<?= (int)$assignment['id'] ?>" class="hover:text-indigo-600 transition-colors">
                                        <?= htmlspecialchars($assignment['title'] ?? '') ?>
                                    </a>
                                </h3>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 <?= $sc['bg'] ?> <?= $sc['text'] ?> rounded-full text-xs font-semibold">
                                    <i class="fas <?= $sc['icon'] ?>"></i> <?= $sc['label'] ?>
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                                <span><i class="fas fa-book text-gray-400 mr-1"></i> <?= htmlspecialchars($assignment['course_name'] ?? '') ?></span>
                                <?php if (!empty($dueDate)): ?>
                                    <span><i class="fas fa-calendar text-gray-400 mr-1"></i> Due: <?= htmlspecialchars(date('M j, Y g:i A', strtotime($dueDate))) ?></span>
                                <?php endif; ?>
                                <?php if ($isTeacher && isset($assignment['submission_count'])): ?>
                                    <span><i class="fas fa-users text-gray-400 mr-1"></i> <?= (int)$assignment['submission_count'] ?> submissions</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="flex-shrink-0">
                            <a href="/assignments/<?= (int)$assignment['id'] ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-indigo-100 hover:text-indigo-700 transition-colors text-sm">
                                View <i class="fas fa-arrow-right"></i>
                            </a>
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
