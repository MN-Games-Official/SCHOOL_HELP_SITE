<?php
/**
 * Student Dashboard Template
 *
 * Expected variables (via extract):
 *   $user, $enrolled_courses, $enrolled_count, $upcoming_assignments,
 *   $recent_grades, $gpa, $notifications
 */

$userName        = htmlspecialchars($user['name'] ?? 'Student');
$enrolledCourses = $enrolled_courses ?? [];
$enrolledCount   = $enrolled_count   ?? 0;
$upcoming        = $upcoming_assignments ?? [];
$grades          = $recent_grades ?? [];
$avgGrade        = $gpa ?? 0;
$notifs          = $notifications ?? [];

$quotes = [
    'The beautiful thing about learning is that nobody can take it away from you.',
    'Education is the passport to the future.',
    'Success is the sum of small efforts repeated day in and day out.',
    'The expert in anything was once a beginner.',
    'Learning never exhausts the mind.',
];
$quote = $quotes[array_rand($quotes)];

$dueCount = count($upcoming);

// Study streak placeholder
$studyStreak = $user['study_streak'] ?? 0;
?>

<!-- Welcome Header -->
<div class="mb-8">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-500 p-8 text-white shadow-lg">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-6 right-20 h-24 w-24 rounded-full bg-white/10"></div>
        <div class="relative z-10">
            <h1 class="text-3xl font-bold">Welcome back, <?= $userName ?>! 👋</h1>
            <p class="mt-2 max-w-2xl text-indigo-100 italic">"<?= htmlspecialchars($quote) ?>"</p>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Enrolled Courses -->
    <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100">
                <i class="fas fa-book-open text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Enrolled Courses</p>
                <p class="text-2xl font-bold text-gray-900"><?= $enrolledCount ?></p>
            </div>
        </div>
    </div>

    <!-- Assignments Due -->
    <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition group-hover:bg-amber-100">
                <i class="fas fa-clipboard-list text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Assignments Due</p>
                <p class="text-2xl font-bold text-gray-900"><?= $dueCount ?></p>
            </div>
        </div>
    </div>

    <!-- Average Grade -->
    <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Average Grade</p>
                <p class="text-2xl font-bold text-gray-900"><?= number_format($avgGrade, 1) ?>%</p>
            </div>
        </div>
    </div>

    <!-- Study Streak -->
    <div class="group relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-rose-50 text-rose-600 transition group-hover:bg-rose-100">
                <i class="fas fa-fire text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Study Streak</p>
                <p class="text-2xl font-bold text-gray-900"><?= (int) $studyStreak ?> <span class="text-sm font-normal text-gray-400">days</span></p>
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
                <h2 class="text-lg font-semibold text-gray-900"><i class="fas fa-graduation-cap mr-2 text-indigo-500"></i>My Courses</h2>
                <?php if ($enrolledCount > 4): ?>
                    <a href="/courses" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition">View All &rarr;</a>
                <?php endif; ?>
            </div>

            <?php if (empty($enrolledCourses)): ?>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-10 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-50">
                        <i class="fas fa-book-open text-2xl text-indigo-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700">No courses yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Browse available courses and start learning today!</p>
                    <a href="/courses" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                        <i class="fas fa-search"></i> Browse Courses
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <?php foreach (array_slice($enrolledCourses, 0, 4) as $course): ?>
                        <?php
                        $colors = ['bg-indigo-500', 'bg-purple-500', 'bg-blue-500', 'bg-emerald-500', 'bg-rose-500', 'bg-amber-500'];
                        $bannerColor = $course['banner_color'] ?? $colors[crc32($course['id'] ?? '') % count($colors)];
                        $progress = $course['progress'] ?? 0;
                        ?>
                        <a href="/courses/<?= htmlspecialchars($course['id'] ?? '') ?>" class="group block rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden transition hover:shadow-md hover:ring-indigo-200">
                            <div class="h-2 <?= htmlspecialchars($bannerColor) ?>"></div>
                            <div class="p-5">
                                <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition line-clamp-1"><?= htmlspecialchars($course['title'] ?? 'Untitled') ?></h3>
                                <p class="mt-1 text-xs text-gray-500">
                                    <i class="fas fa-chalkboard-teacher mr-1"></i>
                                    <?= htmlspecialchars($course['teacher_name'] ?? 'Unknown Teacher') ?>
                                </p>
                                <!-- Progress Bar -->
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                        <span>Progress</span>
                                        <span class="font-medium"><?= (int) $progress ?>%</span>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-100">
                                        <div class="h-2 rounded-full bg-indigo-500 transition-all" style="width: <?= min(100, (int) $progress) ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Upcoming Deadlines -->
        <section>
            <h2 class="mb-4 text-lg font-semibold text-gray-900"><i class="fas fa-clock mr-2 text-amber-500"></i>Upcoming Deadlines</h2>

            <?php if (empty($upcoming)): ?>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-8 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-green-50">
                        <i class="fas fa-check-circle text-2xl text-green-400"></i>
                    </div>
                    <h3 class="font-semibold text-gray-700">All caught up!</h3>
                    <p class="mt-1 text-sm text-gray-500">No upcoming deadlines. Keep up the great work!</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($upcoming as $item): ?>
                        <?php
                        $dueDate  = $item['due_date'] ?? null;
                        $nowTs    = time();
                        $dueTs    = $dueDate ? strtotime($dueDate) : null;
                        $hoursLeft = $dueTs ? ($dueTs - $nowTs) / 3600 : PHP_INT_MAX;

                        if ($hoursLeft < 24) {
                            $urgencyClass = 'border-l-red-500 bg-red-50';
                            $badgeClass   = 'bg-red-100 text-red-700';
                            $badgeLabel   = 'Urgent';
                        } elseif ($hoursLeft < 72) {
                            $urgencyClass = 'border-l-amber-500 bg-amber-50';
                            $badgeClass   = 'bg-amber-100 text-amber-700';
                            $badgeLabel   = 'Soon';
                        } else {
                            $urgencyClass = 'border-l-green-500 bg-white';
                            $badgeClass   = 'bg-green-100 text-green-700';
                            $badgeLabel   = 'Upcoming';
                        }
                        ?>
                        <div class="flex items-center justify-between rounded-lg border-l-4 p-4 shadow-sm ring-1 ring-gray-100 <?= $urgencyClass ?>">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900 truncate"><?= htmlspecialchars($item['title'] ?? 'Untitled') ?></p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <?= htmlspecialchars($item['course_name'] ?? '') ?>
                                    <?php if ($dueDate): ?>
                                        &middot; Due <?= date('M j, g:i A', strtotime($dueDate)) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <span class="ml-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?= $badgeClass ?>">
                                <?= $badgeLabel ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Recent Grades -->
        <section>
            <h2 class="mb-4 text-lg font-semibold text-gray-900"><i class="fas fa-star mr-2 text-emerald-500"></i>Recent Grades</h2>

            <?php if (empty($grades)): ?>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-8 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-gray-50">
                        <i class="fas fa-chart-bar text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="font-semibold text-gray-700">No grades yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Complete assignments and quizzes to see your grades here.</p>
                </div>
            <?php else: ?>
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Item</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Course</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Grade</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($grades as $g): ?>
                                <?php
                                $score = $g['score'] ?? $g['grade'] ?? 0;
                                if ($score >= 90) $gradeColor = 'text-emerald-600 bg-emerald-50';
                                elseif ($score >= 70) $gradeColor = 'text-blue-600 bg-blue-50';
                                elseif ($score >= 50) $gradeColor = 'text-amber-600 bg-amber-50';
                                else $gradeColor = 'text-red-600 bg-red-50';
                                ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($g['title'] ?? $g['assignment_title'] ?? 'N/A') ?></td>
                                    <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-500"><?= htmlspecialchars($g['course_name'] ?? 'N/A') ?></td>
                                    <td class="whitespace-nowrap px-5 py-3 text-right">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold <?= $gradeColor ?>"><?= number_format((float) $score, 1) ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
                <a href="/ai/tutor" class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                        <i class="fas fa-robot text-lg"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700">AI Tutor</span>
                </a>
                <a href="/notes/create" class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                        <i class="fas fa-sticky-note text-lg"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700">Create Note</span>
                </a>
                <a href="/flashcards" class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <i class="fas fa-layer-group text-lg"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700">Flashcards</span>
                </a>
                <a href="/forum" class="flex flex-col items-center gap-2 rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md hover:ring-indigo-200">
                    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                        <i class="fas fa-comments text-lg"></i>
                    </div>
                    <span class="text-xs font-medium text-gray-700">Forum</span>
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
