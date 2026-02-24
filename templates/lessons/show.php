<?php
$lessonTitle = $lesson['title'] ?? 'Lesson';
$courseTitle = $course['title'] ?? 'Course';
$courseId = $course['id'] ?? 0;
$lessonContent = $lesson['content'] ?? '';
$videoUrl = $lesson['video_url'] ?? '';
$duration = $lesson['duration'] ?? 0;
$readingTime = $lesson['reading_time'] ?? ceil(str_word_count(strip_tags($lessonContent)) / 200);
$isCompleted = $completed ?? false;
$isStudent = ($currentUser['role'] ?? '') === 'student';
$lessons = $courseLessons ?? [];
$prevLesson = $previousLesson ?? null;
$nextLesson = $nextLesson ?? null;
$lessonId = $lesson['id'] ?? 0;
?>

<div class="flex flex-col lg:flex-row gap-8">
    <!-- Main content -->
    <div class="flex-1 min-w-0">
        <!-- Breadcrumb -->
        <nav class="flex items-center text-sm mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1.5 flex-wrap">
                <li><a href="/courses" class="text-gray-400 hover:text-indigo-600 transition-colors"><i class="fas fa-home text-xs"></i></a></li>
                <li class="flex items-center gap-1.5">
                    <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                    <a href="/courses/<?= (int)$courseId ?>" class="text-gray-500 hover:text-indigo-600 transition-colors"><?= htmlspecialchars($courseTitle) ?></a>
                </li>
                <li class="flex items-center gap-1.5">
                    <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                    <span class="font-medium text-gray-700"><?= htmlspecialchars($lessonTitle) ?></span>
                </li>
            </ol>
        </nav>

        <!-- Lesson header -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-medium">
                    <i class="fas fa-clock"></i> <?= (int)$duration ?> min
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                    <i class="fas fa-book-open"></i> <?= (int)$readingTime ?> min read
                </span>
                <?php if ($isCompleted): ?>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                        <i class="fas fa-check-circle"></i> Completed
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900"><?= htmlspecialchars($lessonTitle) ?></h1>
        </div>

        <!-- Video embed -->
        <?php if (!empty($videoUrl)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="aspect-video bg-gray-900 flex items-center justify-center">
                <iframe src="<?= htmlspecialchars($videoUrl) ?>" class="w-full h-full" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 flex flex-col items-center justify-center text-gray-400">
                <i class="fas fa-play-circle text-5xl mb-3"></i>
                <p class="text-sm font-medium">No video available for this lesson</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Lesson content -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
            <div class="prose prose-indigo max-w-none
                prose-headings:text-gray-900 prose-headings:font-bold
                prose-p:text-gray-600 prose-p:leading-relaxed
                prose-a:text-indigo-600 prose-a:no-underline hover:prose-a:underline
                prose-strong:text-gray-900
                prose-code:bg-gray-100 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded prose-code:text-sm
                prose-pre:bg-gray-900 prose-pre:text-gray-100
                prose-img:rounded-xl prose-img:shadow-md
                prose-blockquote:border-indigo-500 prose-blockquote:bg-indigo-50 prose-blockquote:rounded-r-lg prose-blockquote:py-1
                prose-ul:text-gray-600 prose-ol:text-gray-600
                prose-li:marker:text-indigo-400">
                <?= $lessonContent ?>
            </div>
        </div>

        <!-- Mark as Complete -->
        <?php if ($isStudent && !$isCompleted): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <form action="/lessons/<?= (int)$lessonId ?>/complete" method="POST" class="flex items-center justify-between">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                <div>
                    <h3 class="font-semibold text-gray-900">Finished this lesson?</h3>
                    <p class="text-sm text-gray-500">Mark it as complete to track your progress.</p>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors shadow-sm hover:shadow-md">
                    <i class="fas fa-check"></i> Mark as Complete
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Previous/Next navigation -->
        <div class="flex items-center justify-between gap-4">
            <?php if ($prevLesson): ?>
                <a href="/lessons/<?= (int)$prevLesson['id'] ?>" class="flex-1 group flex items-center gap-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                        <i class="fas fa-arrow-left text-gray-400 group-hover:text-indigo-600"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400 font-medium">Previous</p>
                        <p class="text-sm font-semibold text-gray-700 truncate"><?= htmlspecialchars($prevLesson['title'] ?? '') ?></p>
                    </div>
                </a>
            <?php else: ?>
                <div class="flex-1"></div>
            <?php endif; ?>

            <?php if ($nextLesson): ?>
                <a href="/lessons/<?= (int)$nextLesson['id'] ?>" class="flex-1 group flex items-center justify-end gap-3 p-4 bg-white rounded-xl border border-gray-200 hover:border-indigo-300 hover:shadow-md transition-all text-right">
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400 font-medium">Next</p>
                        <p class="text-sm font-semibold text-gray-700 truncate"><?= htmlspecialchars($nextLesson['title'] ?? '') ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-gray-100 group-hover:bg-indigo-100 flex items-center justify-center transition-colors">
                        <i class="fas fa-arrow-right text-gray-400 group-hover:text-indigo-600"></i>
                    </div>
                </a>
            <?php else: ?>
                <div class="flex-1"></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar: Lesson list -->
    <aside class="w-full lg:w-80 flex-shrink-0">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 sticky top-24 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Course Lessons</h3>
                <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($courseTitle) ?></p>
            </div>
            <div class="max-h-[60vh] overflow-y-auto">
                <?php foreach ($lessons as $i => $l): ?>
                    <?php $isCurrent = ((int)($l['id'] ?? 0) === (int)$lessonId); ?>
                    <a href="/lessons/<?= (int)$l['id'] ?>"
                       class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 transition-colors <?= $isCurrent ? 'bg-indigo-50 border-l-4 border-l-indigo-600' : 'hover:bg-gray-50' ?>">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 <?= $isCurrent ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $i + 1 ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium truncate <?= $isCurrent ? 'text-indigo-700' : 'text-gray-700' ?>"><?= htmlspecialchars($l['title'] ?? '') ?></p>
                            <?php if (!empty($l['duration'])): ?>
                                <p class="text-xs text-gray-400"><?= (int)$l['duration'] ?> min</p>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($l['completed'])): ?>
                            <i class="fas fa-check-circle text-green-500 text-sm flex-shrink-0"></i>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</div>
