<?php
/**
 * Course Listing Template
 *
 * Expected variables (via extract):
 *   $user, $courses, $search, $subject, $subjects
 */

$allCourses    = $courses  ?? [];
$searchQuery   = $search   ?? '';
$activeSubject = $subject  ?? '';
$subjectList   = $subjects ?? [];
$currentUser   = $user     ?? null;
$isTeacher     = ($currentUser['role'] ?? '') === 'teacher' || ($currentUser['role'] ?? '') === 'admin';

// Simple client-side pagination
$perPage     = 9;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$totalItems  = count($allCourses);
$totalPages  = max(1, (int) ceil($totalItems / $perPage));
$currentPage = min($currentPage, $totalPages);
$paginated   = array_slice($allCourses, ($currentPage - 1) * $perPage, $perPage);

$difficultyColors = [
    'Beginner'     => 'bg-green-100 text-green-700',
    'Intermediate' => 'bg-amber-100 text-amber-700',
    'Advanced'     => 'bg-red-100 text-red-700',
];
?>

<!-- Page Header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Courses</h1>
        <p class="mt-1 text-sm text-gray-500">Browse and discover courses to accelerate your learning.</p>
    </div>
</div>

<!-- Search & Filters -->
<form method="GET" action="/courses" class="mb-8">
    <div class="flex flex-col gap-3 sm:flex-row">
        <!-- Search -->
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" value="<?= htmlspecialchars($searchQuery) ?>"
                   placeholder="Search courses..."
                   class="block w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition">
        </div>
        <!-- Subject Filter -->
        <select name="subject"
                class="rounded-xl border border-gray-300 bg-white py-2.5 px-4 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition sm:w-52">
            <option value="">All Subjects</option>
            <?php foreach ($subjectList as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $activeSubject === $s ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Difficulty Filter -->
        <select name="difficulty"
                class="rounded-xl border border-gray-300 bg-white py-2.5 px-4 text-sm text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition sm:w-44">
            <option value="">All Levels</option>
            <option value="Beginner" <?= ($_GET['difficulty'] ?? '') === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
            <option value="Intermediate" <?= ($_GET['difficulty'] ?? '') === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
            <option value="Advanced" <?= ($_GET['difficulty'] ?? '') === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
        </select>
        <!-- Submit -->
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-200 transition">
            <i class="fas fa-filter text-xs"></i> Filter
        </button>
    </div>
</form>

<!-- Course Grid -->
<?php if (empty($paginated)): ?>
    <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-16 text-center">
        <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-indigo-50">
            <i class="fas fa-book-open text-3xl text-indigo-400"></i>
        </div>
        <h3 class="text-xl font-semibold text-gray-700">No courses found</h3>
        <p class="mt-2 text-gray-500">Try adjusting your search or filters.</p>
        <?php if ($searchQuery !== '' || $activeSubject !== ''): ?>
            <a href="/courses" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                <i class="fas fa-times"></i> Clear Filters
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($paginated as $course): ?>
            <?php
            $colors      = ['bg-indigo-500', 'bg-purple-500', 'bg-blue-500', 'bg-emerald-500', 'bg-rose-500', 'bg-cyan-500', 'bg-amber-500'];
            $bannerColor = $course['banner_color'] ?? $colors[crc32($course['id'] ?? '') % count($colors)];
            $difficulty  = $course['difficulty'] ?? '';
            $diffClass   = $difficultyColors[$difficulty] ?? 'bg-gray-100 text-gray-600';
            $enrolled    = $currentUser && in_array($currentUser['id'], $course['enrolled_students'] ?? []);
            $studentNum  = count($course['enrolled_students'] ?? []);
            $lessonNum   = $course['lesson_count'] ?? 0;
            ?>
            <div class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 transition hover:shadow-lg hover:ring-indigo-200">
                <!-- Banner -->
                <div class="h-36 <?= htmlspecialchars($bannerColor) ?> relative flex items-end p-5">
                    <?php if ($difficulty): ?>
                        <span class="absolute right-3 top-3 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold <?= $diffClass ?> backdrop-blur">
                            <?= htmlspecialchars($difficulty) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($course['subject'])): ?>
                        <span class="inline-flex items-center rounded-full bg-white/90 px-2.5 py-0.5 text-xs font-medium text-gray-700 shadow-sm">
                            <?= htmlspecialchars($course['subject']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Content -->
                <div class="flex flex-1 flex-col p-5">
                    <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition line-clamp-1">
                        <a href="/courses/<?= htmlspecialchars($course['id'] ?? '') ?>"><?= htmlspecialchars($course['title'] ?? 'Untitled') ?></a>
                    </h3>
                    <p class="mt-1.5 text-sm text-gray-500 line-clamp-2 flex-1"><?= htmlspecialchars($course['description'] ?? '') ?></p>

                    <!-- Teacher -->
                    <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 text-[10px] font-bold">
                            <?= strtoupper(substr($course['teacher_name'] ?? 'T', 0, 1)) ?>
                        </div>
                        <span><?= htmlspecialchars($course['teacher_name'] ?? 'Unknown') ?></span>
                    </div>

                    <!-- Meta -->
                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-xs text-gray-400">
                        <span><i class="fas fa-users mr-1"></i><?= $studentNum ?> students</span>
                        <span><i class="fas fa-list mr-1"></i><?= $lessonNum ?> lessons</span>
                    </div>

                    <!-- Action -->
                    <div class="mt-4">
                        <?php if ($enrolled): ?>
                            <span class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-50 py-2.5 text-sm font-medium text-emerald-700">
                                <i class="fas fa-check-circle"></i> Enrolled
                            </span>
                        <?php else: ?>
                            <a href="/courses/<?= htmlspecialchars($course['id'] ?? '') ?>"
                               class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                                View Course
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-8">
            <?php
            $baseUrl = '/courses?' . http_build_query(array_filter([
                'search'     => $searchQuery,
                'subject'    => $activeSubject,
                'difficulty' => $_GET['difficulty'] ?? '',
            ]));
            include __DIR__ . '/../components/pagination.php';
            ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Floating Create Button (teachers only) -->
<?php if ($isTeacher): ?>
    <a href="/courses/create"
       class="fixed bottom-8 right-8 z-20 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg hover:bg-indigo-700 hover:shadow-xl focus:ring-4 focus:ring-indigo-200 transition-all"
       title="Create Course">
        <i class="fas fa-plus text-xl"></i>
    </a>
<?php endif; ?>
