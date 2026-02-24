<?php
$quiz = $quiz ?? [];
$quizId = $quiz['id'] ?? 0;
$attempts = $attempts ?? [];
$isTeacher = ($currentUser['role'] ?? '') === 'teacher';
$isStudent = ($currentUser['role'] ?? '') === 'student';
$canTake = $canTake ?? false;
$maxAttempts = $quiz['max_attempts'] ?? 0;
$attemptCount = count($attempts);
?>

<div class="max-w-4xl mx-auto">
    <!-- Back link -->
    <a href="/quizzes" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-6">
        <i class="fas fa-arrow-left"></i> Back to Quizzes
    </a>

    <!-- Quiz Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
        <span class="inline-block px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-medium mb-3">
            <?= htmlspecialchars($quiz['course_name'] ?? 'General') ?>
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($quiz['title'] ?? '') ?></h1>
        <?php if (!empty($quiz['description'])): ?>
            <p class="text-gray-600 leading-relaxed"><?= htmlspecialchars($quiz['description']) ?></p>
        <?php endif; ?>

        <!-- Teacher actions -->
        <?php if ($isTeacher): ?>
            <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-3">
                <a href="/quizzes/<?= (int)$quizId ?>/results" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-50 text-purple-700 font-semibold rounded-lg hover:bg-purple-100 transition-colors text-sm">
                    <i class="fas fa-chart-bar"></i> View Results
                </a>
                <a href="/quizzes/<?= (int)$quizId ?>/edit" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition-colors text-sm">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form action="/quizzes/<?= (int)$quizId ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this quiz?')">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 text-red-600 font-semibold rounded-lg hover:bg-red-50 transition-colors text-sm">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Info Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-question-circle text-indigo-600 text-xl"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?= (int)($quiz['question_count'] ?? 0) ?></p>
            <p class="text-xs text-gray-500 mt-1">Questions</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-clock text-blue-600 text-xl"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?= (int)($quiz['time_limit'] ?? 0) ?></p>
            <p class="text-xs text-gray-500 mt-1">Minutes</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-signal text-purple-600 text-xl"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900 capitalize"><?= htmlspecialchars($quiz['difficulty'] ?? 'Medium') ?></p>
            <p class="text-xs text-gray-500 mt-1">Difficulty</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-trophy text-green-600 text-xl"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?= (int)($quiz['passing_score'] ?? 70) ?>%</p>
            <p class="text-xs text-gray-500 mt-1">Passing Score</p>
        </div>
    </div>

    <!-- Previous Attempts -->
    <?php if (!empty($attempts)): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Your Previous Attempts</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Attempt</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Time Taken</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($attempts as $i => $attempt): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">#<?= $i + 1 ?></td>
                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($attempt['created_at'] ?? '') ?></td>
                            <td class="px-6 py-4">
                                <span class="font-bold <?= ($attempt['score'] ?? 0) >= ($quiz['passing_score'] ?? 70) ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= (int)($attempt['score'] ?? 0) ?>%
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (($attempt['score'] ?? 0) >= ($quiz['passing_score'] ?? 70)): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                        <i class="fas fa-check-circle"></i> Passed
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">
                                        <i class="fas fa-times-circle"></i> Failed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($attempt['time_taken'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-right">
                                <a href="/quizzes/<?= (int)$quizId ?>/results/<?= (int)$attempt['id'] ?>" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                    Review <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Take Quiz Button -->
    <?php if ($isStudent): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
        <?php if ($canTake): ?>
            <p class="text-gray-500 mb-4">
                <?php if ($maxAttempts > 0): ?>
                    You have <strong class="text-gray-900"><?= $maxAttempts - $attemptCount ?></strong> attempt(s) remaining.
                <?php else: ?>
                    Unlimited attempts allowed.
                <?php endif; ?>
            </p>
            <a href="/quizzes/<?= (int)$quizId ?>/take" class="inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-md hover:shadow-lg text-lg">
                <i class="fas fa-play"></i> Take Quiz
            </a>
        <?php else: ?>
            <div class="text-gray-400">
                <i class="fas fa-ban text-4xl mb-3"></i>
                <p class="font-semibold text-gray-600">Maximum attempts reached</p>
                <p class="text-sm mt-1">You have used all <?= (int)$maxAttempts ?> attempt(s) for this quiz.</p>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
