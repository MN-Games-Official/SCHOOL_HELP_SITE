<?php
/**
 * Single Course Detail Template
 *
 * Expected variables (via extract):
 *   $user, $course, $lessons, $is_enrolled, $stats
 */

$currentUser = $user    ?? null;
$courseData  = $course  ?? [];
$lessonList = $lessons ?? [];
$enrolled   = $is_enrolled ?? false;
$courseStats = $stats   ?? [];

$courseId     = $courseData['id'] ?? '';
$courseTitle  = $courseData['title'] ?? 'Untitled Course';
$description = $courseData['description'] ?? '';
$subject     = $courseData['subject'] ?? '';
$difficulty  = $courseData['difficulty'] ?? '';
$teacherName = $courseData['teacher_name'] ?? 'Unknown Teacher';
$teacherId   = $courseData['teacher_id'] ?? '';
$createdAt   = $courseData['created_at'] ?? '';
$studentCount = count($courseData['enrolled_students'] ?? []);
$syllabus    = $courseData['syllabus'] ?? '';
$prerequisites = $courseData['prerequisites'] ?? '';

$isOwner = $currentUser && ($currentUser['id'] === $teacherId || ($currentUser['role'] ?? '') === 'admin');

$colors      = ['bg-indigo-600', 'bg-purple-600', 'bg-blue-600', 'bg-emerald-600', 'bg-rose-600', 'bg-cyan-600'];
$bannerColor = $courseData['banner_color'] ?? $colors[crc32($courseId) % count($colors)];

$difficultyColors = [
    'Beginner'     => 'bg-green-100 text-green-700',
    'Intermediate' => 'bg-amber-100 text-amber-700',
    'Advanced'     => 'bg-red-100 text-red-700',
];
$diffClass = $difficultyColors[$difficulty] ?? 'bg-gray-100 text-gray-600';

// Calculate progress for enrolled students
$totalLessons    = count($lessonList);
$completedLessons = 0;
if ($enrolled && $currentUser) {
    foreach ($lessonList as $l) {
        $completions = $l['completed_by'] ?? [];
        if (in_array($currentUser['id'], $completions)) {
            $completedLessons++;
        }
    }
}
$progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
?>

<!-- Course Header / Banner -->
<div class="relative -mx-4 -mt-6 mb-8 sm:-mx-6 lg:-mx-8">
    <div class="<?= htmlspecialchars($bannerColor) ?> relative overflow-hidden px-4 pb-8 pt-12 sm:px-6 lg:px-8">
        <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-10 left-1/3 h-32 w-32 rounded-full bg-white/10"></div>

        <div class="relative z-10 mx-auto max-w-4xl">
            <!-- Breadcrumb -->
            <nav class="mb-4 text-sm">
                <ol class="flex items-center gap-2 text-white/70">
                    <li><a href="/courses" class="hover:text-white transition">Courses</a></li>
                    <li><i class="fas fa-chevron-right text-[10px]"></i></li>
                    <li class="text-white font-medium truncate"><?= htmlspecialchars($courseTitle) ?></li>
                </ol>
            </nav>

            <h1 class="text-3xl font-bold text-white sm:text-4xl"><?= htmlspecialchars($courseTitle) ?></h1>
            <p class="mt-3 max-w-2xl text-white/80 text-sm sm:text-base"><?= htmlspecialchars($description) ?></p>

            <!-- Badges -->
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <?php if ($subject): ?>
                    <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                        <i class="fas fa-tag"></i> <?= htmlspecialchars($subject) ?>
                    </span>
                <?php endif; ?>
                <?php if ($difficulty): ?>
                    <span class="inline-flex items-center gap-1 rounded-full <?= $diffClass ?> px-3 py-1 text-xs font-semibold">
                        <?= htmlspecialchars($difficulty) ?>
                    </span>
                <?php endif; ?>
                <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                    <i class="fas fa-users"></i> <?= $studentCount ?> student<?= $studentCount !== 1 ? 's' : '' ?>
                </span>
                <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                    <i class="fas fa-list"></i> <?= $totalLessons ?> lesson<?= $totalLessons !== 1 ? 's' : '' ?>
                </span>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-wrap items-center gap-3">
                <?php if ($enrolled): ?>
                    <span class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-emerald-600 shadow">
                        <i class="fas fa-check-circle"></i> Enrolled
                    </span>
                <?php elseif ($currentUser && !$isOwner): ?>
                    <form method="POST" action="/courses/<?= htmlspecialchars($courseId) ?>/enroll">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-indigo-600 shadow hover:bg-gray-50 transition">
                            <i class="fas fa-plus-circle"></i> Enroll Now
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($isOwner): ?>
                    <a href="/courses/<?= htmlspecialchars($courseId) ?>/edit"
                       class="inline-flex items-center gap-2 rounded-xl bg-white/20 px-5 py-2.5 text-sm font-medium text-white backdrop-blur hover:bg-white/30 transition">
                        <i class="fas fa-edit"></i> Edit Course
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto max-w-4xl">
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Main Content (2/3) -->
        <div class="space-y-8 lg:col-span-2">

            <!-- Progress Bar (enrolled students) -->
            <?php if ($enrolled): ?>
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-700">Your Progress</h3>
                        <span class="text-sm font-bold text-indigo-600"><?= $progress ?>%</span>
                    </div>
                    <div class="h-3 w-full rounded-full bg-gray-100">
                        <div class="h-3 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all" style="width: <?= $progress ?>%"></div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500"><?= $completedLessons ?> of <?= $totalLessons ?> lessons completed</p>
                </div>
            <?php endif; ?>

            <!-- Lessons -->
            <section>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900"><i class="fas fa-list-ol mr-2 text-indigo-500"></i>Lessons</h2>
                    <?php if ($isOwner): ?>
                        <a href="/courses/<?= htmlspecialchars($courseId) ?>/lessons/create"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                            <i class="fas fa-plus text-xs"></i> Add Lesson
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($lessonList)): ?>
                    <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-10 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-50">
                            <i class="fas fa-book text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-700">No lessons yet</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            <?= $isOwner ? 'Start adding lessons to your course.' : 'Lessons will appear here once the teacher adds them.' ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($lessonList as $idx => $lesson): ?>
                            <?php
                            $isCompleted = false;
                            if ($enrolled && $currentUser) {
                                $completions = $lesson['completed_by'] ?? [];
                                $isCompleted = in_array($currentUser['id'], $completions);
                            }
                            $duration = $lesson['duration'] ?? null;
                            ?>
                            <div class="flex items-center gap-4 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200 transition hover:shadow-md">
                                <!-- Number / Check -->
                                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full <?= $isCompleted ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500' ?> text-sm font-bold">
                                    <?php if ($isCompleted): ?>
                                        <i class="fas fa-check"></i>
                                    <?php else: ?>
                                        <?= $idx + 1 ?>
                                    <?php endif; ?>
                                </div>
                                <!-- Info -->
                                <div class="min-w-0 flex-1">
                                    <a href="/lessons/<?= htmlspecialchars($lesson['id'] ?? '') ?>"
                                       class="font-medium text-gray-900 hover:text-indigo-600 transition truncate block">
                                        <?= htmlspecialchars($lesson['title'] ?? 'Untitled Lesson') ?>
                                    </a>
                                    <?php if ($duration): ?>
                                        <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-clock mr-1"></i><?= htmlspecialchars($duration) ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($isCompleted): ?>
                                    <span class="text-xs font-medium text-emerald-600">Completed</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Assignments & Quizzes placeholders -->
            <section>
                <h2 class="mb-4 text-lg font-semibold text-gray-900"><i class="fas fa-clipboard-list mr-2 text-amber-500"></i>Assignments</h2>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-8 text-center">
                    <p class="text-sm text-gray-500"><a href="/assignments?course=<?= htmlspecialchars($courseId) ?>" class="text-indigo-600 hover:underline">View assignments</a> for this course.</p>
                </div>
            </section>

            <section>
                <h2 class="mb-4 text-lg font-semibold text-gray-900"><i class="fas fa-question-circle mr-2 text-purple-500"></i>Quizzes</h2>
                <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-8 text-center">
                    <p class="text-sm text-gray-500"><a href="/quizzes?course=<?= htmlspecialchars($courseId) ?>" class="text-indigo-600 hover:underline">View quizzes</a> for this course.</p>
                </div>
            </section>

            <!-- Students (owner only) -->
            <?php if ($isOwner): ?>
                <section>
                    <h2 class="mb-4 text-lg font-semibold text-gray-900"><i class="fas fa-user-graduate mr-2 text-emerald-500"></i>Enrolled Students</h2>
                    <?php
                    $enrolledIds = $courseData['enrolled_students'] ?? [];
                    ?>
                    <?php if (empty($enrolledIds)): ?>
                        <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white p-8 text-center">
                            <p class="text-sm text-gray-500">No students enrolled yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 p-5">
                            <p class="text-sm text-gray-600"><strong><?= count($enrolledIds) ?></strong> student<?= count($enrolledIds) !== 1 ? 's' : '' ?> enrolled in this course.</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
            <!-- Teacher Card -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200 text-center">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-xl font-bold">
                    <?= strtoupper(substr($teacherName, 0, 1)) ?>
                </div>
                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($teacherName) ?></h3>
                <p class="text-xs text-gray-500">Instructor</p>
            </div>

            <!-- Course Info -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h3 class="mb-4 text-sm font-semibold text-gray-900">Course Details</h3>
                <dl class="space-y-3 text-sm">
                    <?php if ($subject): ?>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Subject</dt>
                            <dd class="font-medium text-gray-900"><?= htmlspecialchars($subject) ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($difficulty): ?>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Level</dt>
                            <dd><span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold <?= $diffClass ?>"><?= htmlspecialchars($difficulty) ?></span></dd>
                        </div>
                    <?php endif; ?>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Lessons</dt>
                        <dd class="font-medium text-gray-900"><?= $totalLessons ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Students</dt>
                        <dd class="font-medium text-gray-900"><?= $studentCount ?></dd>
                    </div>
                    <?php if ($createdAt): ?>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Created</dt>
                            <dd class="font-medium text-gray-900"><?= date('M j, Y', strtotime($createdAt)) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- Syllabus -->
            <?php if ($syllabus): ?>
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Syllabus</h3>
                    <div class="prose prose-sm text-gray-600 max-w-none"><?= nl2br(htmlspecialchars($syllabus)) ?></div>
                </div>
            <?php endif; ?>

            <!-- Prerequisites -->
            <?php if ($prerequisites): ?>
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="mb-3 text-sm font-semibold text-gray-900">Prerequisites</h3>
                    <div class="prose prose-sm text-gray-600 max-w-none"><?= nl2br(htmlspecialchars($prerequisites)) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
