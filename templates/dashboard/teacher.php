<?php
/**
 * Teacher Dashboard Template
 *
 * Expected variables (via extract):
 *   $user, $courses, $course_count, $student_count,
 *   $assignments, $pending_submissions, $notifications
 */

$userName           = htmlspecialchars($user['name'] ?? 'Teacher');
$teacherCourses     = $courses ?? [];
$courseCount         = $course_count ?? 0;
$studentCount       = $student_count ?? 0;
$pendingCount       = $pending_submissions ?? 0;
$teacherAssignments = $assignments ?? [];
$notifs             = $notifications ?? [];

// Average score placeholder
$avgScore = 0;
$scoreItems = 0;
foreach ($teacherAssignments as $a) {
    if (isset($a['average_score'])) {
        $avgScore += (float) $a['average_score'];
        $scoreItems++;
    }
}
$avgScore = $scoreItems > 0 ? round($avgScore / $scoreItems, 1) : 0;
?>

<!-- Welcome Header -->
<div class="mb-8">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-500 p-8 text-white shadow-lg">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-6 right-24 h-24 w-24 rounded-full bg-white/10"></div>
        <div class="relative z-10">
            <h1 class="text-3xl font-bold">Hello, <?= $userName ?>! 🎓</h1>
            <p class="mt-2 text-emerald-100">Here&rsquo;s an overview of your teaching activity.</p>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Courses Created -->
    <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100">
                <i class="fas fa-chalkboard text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Courses Created</p>
                <p class="text-2xl font-bold text-gray-900"><?= $courseCount ?></p>
            </div>
        </div>
    </div>

    <!-- Total Students -->
    <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Total Students</p>
                <p class="text-2xl font-bold text-gray-900"><?= $studentCount ?></p>
            </div>
        </div>
    </div>

    <!-- Pending Reviews -->
    <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition group-hover:bg-amber-100">
                <i class="fas fa-hourglass-half text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Pending Reviews</p>
                <p class="text-2xl font-bold text-gray-900"><?= $pendingCount ?></p>
            </div>
        </div>
    </div>

    <!-- Average Score -->
    <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50 text-purple-600 transition group-hover:bg-purple-100">
                <i class="fas fa-chart-bar text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Average Score</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($avgScore, 1) ?>%</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
    <!-- Left Column (2/3) -->
    <div class="space-y-8 lg:col-span-2">

        <!-- My Courses -->
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900"><i class="fas fa-book mr-2 text-indigo-500"></i>My Courses</h2>
                <a href="/courses/create" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-plus text-xs"></i> New Course
                </a>
            </div>

            <?php if (empty($teacherCourses)): ?>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-10 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50">
                        <i class="fas fa-chalkboard text-2xl text-indigo-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">No courses yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Create your first course and start teaching!</p>
                    <a href="/courses/create" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                        <i class="fas fa-plus"></i> Create Course
                    </a>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($teacherCourses as $course): ?>
                        <?php
                        $studentsEnrolled = count($course['enrolled_students'] ?? []);
                        $lessonCount      = $course['lesson_count'] ?? 0;
                        ?>
                        <a href="/courses/<?= htmlspecialchars($course['id'] ?? '') ?>" class="group flex items-center justify-between rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                            <div class="min-w-0 flex-1">
                                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition truncate"><?= htmlspecialchars($course['title'] ?? 'Untitled') ?></h3>
                                <div class="mt-1 flex items-center gap-4 text-xs text-gray-500">
                                    <span><i class="fas fa-users mr-1"></i><?= $studentsEnrolled ?> students</span>
                                    <span><i class="fas fa-list mr-1"></i><?= $lessonCount ?> lessons</span>
                                    <?php if (!empty($course['subject'])): ?>
                                        <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-600"><?= htmlspecialchars($course['subject']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-300 group-hover:text-indigo-400 transition ml-4"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Pending Submissions -->
        <section>
            <h2 class="mb-4 text-lg font-semibold text-gray-900"><i class="fas fa-inbox mr-2 text-amber-500"></i>Pending Submissions</h2>

            <?php if ($pendingCount === 0): ?>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-8 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-green-50">
                        <i class="fas fa-check-circle text-2xl text-green-400"></i>
                    </div>
                    <h3 class="font-semibold text-gray-700">All caught up!</h3>
                    <p class="mt-1 text-sm text-gray-500">No submissions waiting for your review.</p>
                </div>
            <?php else: ?>
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-6">
                    <div class="flex items-center gap-3 text-amber-600">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <p class="font-medium">You have <span class="font-bold"><?= $pendingCount ?></span> submission<?= $pendingCount !== 1 ? 's' : '' ?> to review.</p>
                    </div>
                    <a href="/assignments" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-amber-50 px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-100 transition">
                        <i class="fas fa-arrow-right text-xs"></i> Review Submissions
                    </a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recent Activity -->
        <section>
            <h2 class="mb-4 text-lg font-semibold text-gray-900"><i class="fas fa-history mr-2 text-blue-500"></i>Recent Activity</h2>

            <?php if (empty($notifs)): ?>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-8 text-center">
                    <i class="fas fa-stream text-2xl text-gray-300 mb-2"></i>
                    <p class="text-sm text-gray-500">No recent activity to display.</p>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach (array_slice($notifs, 0, 8) as $activity): ?>
                        <div class="flex items-start gap-3 rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
                            <div class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-500 flex-shrink-0">
                                <i class="fas fa-circle-info text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-800"><?= htmlspecialchars($activity['message'] ?? $activity['text'] ?? '') ?></p>
                                <p class="mt-0.5 text-xs text-gray-400">
                                    <?php if (!empty($activity['created_at'])): ?>
                                        <?= date('M j, g:i A', strtotime($activity['created_at'])) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Right Column (1/3) -->
    <div class="space-y-8">

        <!-- Quick Actions -->
        <section>
            <h2 class="mb-4 text-lg font-semibold text-gray-900"><i class="fas fa-bolt mr-2 text-yellow-500"></i>Quick Actions</h2>
            <div class="grid grid-cols-2 gap-3">
                <a href="/courses/create" class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                        <i class="fas fa-plus-circle text-lg"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700 text-center">Create Course</span>
                </a>
                <a href="/quizzes/create" class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                        <i class="fas fa-question-circle text-lg"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700 text-center">Create Quiz</span>
                </a>
                <a href="/assignments/create" class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <i class="fas fa-tasks text-lg"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700 text-center">Assignment</span>
                </a>
                <a href="/forum" class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <i class="fas fa-comments text-lg"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700 text-center">View Forum</span>
                </a>
            </div>
        </section>

        <!-- Notifications -->
        <section>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900"><i class="fas fa-bell mr-2 text-indigo-500"></i>Notifications</h2>
                <?php if (!empty($notifs)): ?>
                    <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700"><?= count($notifs) ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($notifs)): ?>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-6 text-center">
                    <i class="fas fa-bell-slash text-2xl text-gray-300 mb-2"></i>
                    <p class="text-sm text-gray-500">No new notifications</p>
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach (array_slice($notifs, 0, 5) as $notif): ?>
                        <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200 transition hover:bg-gray-50">
                            <p class="text-sm text-gray-800"><?= htmlspecialchars($notif['message'] ?? $notif['text'] ?? '') ?></p>
                            <p class="mt-1 text-xs text-gray-400">
                                <?php if (!empty($notif['created_at'])): ?>
                                    <?= date('M j, g:i A', strtotime($notif['created_at'])) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
