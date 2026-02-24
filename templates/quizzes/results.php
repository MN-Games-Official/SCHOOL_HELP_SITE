<?php
$quiz = $quiz ?? [];
$quizId = $quiz['id'] ?? 0;
$result = $result ?? [];
$score = (int)($result['score'] ?? 0);
$passingScore = (int)($quiz['passing_score'] ?? 70);
$passed = $score >= $passingScore;
$correct = (int)($result['correct'] ?? 0);
$incorrect = (int)($result['incorrect'] ?? 0);
$unanswered = (int)($result['unanswered'] ?? 0);
$timeTaken = $result['time_taken'] ?? '-';
$questions = $reviewQuestions ?? [];
$canRetake = $canRetake ?? false;
?>

<div class="max-w-4xl mx-auto">
    <!-- Score Display -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sm:p-12 mb-6 text-center">
        <!-- Score circle -->
        <div class="relative w-40 h-40 mx-auto mb-6">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="52" stroke="#e5e7eb" stroke-width="10" fill="none"/>
                <circle cx="60" cy="60" r="52" stroke="<?= $passed ? '#22c55e' : '#ef4444' ?>" stroke-width="10" fill="none"
                        stroke-dasharray="<?= 2 * 3.14159 * 52 ?>"
                        stroke-dashoffset="<?= 2 * 3.14159 * 52 * (1 - $score / 100) ?>"
                        stroke-linecap="round"
                        class="transition-all duration-1000"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-4xl font-extrabold <?= $passed ? 'text-green-600' : 'text-red-600' ?>"><?= $score ?>%</span>
            </div>
        </div>

        <!-- Pass/Fail badge -->
        <?php if ($passed): ?>
            <span class="inline-flex items-center gap-2 px-5 py-2 bg-green-100 text-green-700 rounded-full text-lg font-bold">
                <i class="fas fa-trophy"></i> Passed!
            </span>
        <?php else: ?>
            <span class="inline-flex items-center gap-2 px-5 py-2 bg-red-100 text-red-700 rounded-full text-lg font-bold">
                <i class="fas fa-times-circle"></i> Not Passed
            </span>
        <?php endif; ?>

        <h1 class="text-2xl font-bold text-gray-900 mt-4"><?= htmlspecialchars($quiz['title'] ?? 'Quiz') ?></h1>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-check text-green-600"></i>
            </div>
            <p class="text-2xl font-bold text-green-600"><?= $correct ?></p>
            <p class="text-xs text-gray-500 mt-1">Correct</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-times text-red-600"></i>
            </div>
            <p class="text-2xl font-bold text-red-600"><?= $incorrect ?></p>
            <p class="text-xs text-gray-500 mt-1">Incorrect</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-minus-circle text-gray-500"></i>
            </div>
            <p class="text-2xl font-bold text-gray-600"><?= $unanswered ?></p>
            <p class="text-xs text-gray-500 mt-1">Unanswered</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 text-center">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                <i class="fas fa-stopwatch text-blue-600"></i>
            </div>
            <p class="text-2xl font-bold text-blue-600"><?= htmlspecialchars($timeTaken) ?></p>
            <p class="text-xs text-gray-500 mt-1">Time Taken</p>
        </div>
    </div>

    <!-- Question Review -->
    <?php if (!empty($questions)): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Question Review</h2>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($questions as $qIndex => $q): ?>
                <?php
                $studentAnswer = $q['student_answer'] ?? null;
                $correctAnswer = $q['correct_answer'] ?? null;
                $isCorrect = $studentAnswer !== null && $studentAnswer == $correctAnswer;
                ?>
                <div class="p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-sm font-bold <?= $isCorrect ? 'bg-green-100 text-green-700' : ($studentAnswer === null ? 'bg-gray-100 text-gray-500' : 'bg-red-100 text-red-700') ?>">
                            <?php if ($isCorrect): ?>
                                <i class="fas fa-check"></i>
                            <?php elseif ($studentAnswer === null): ?>
                                <i class="fas fa-minus"></i>
                            <?php else: ?>
                                <i class="fas fa-times"></i>
                            <?php endif; ?>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 font-medium mb-1">Question <?= $qIndex + 1 ?></p>
                            <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($q['text'] ?? '') ?></h3>
                        </div>
                    </div>
                    <div class="space-y-2 ml-11">
                        <?php foreach (($q['options'] ?? []) as $opt): ?>
                            <?php
                            $optId = $opt['id'] ?? '';
                            $isStudentPick = $studentAnswer == $optId;
                            $isCorrectOpt = $correctAnswer == $optId;
                            $optClass = '';
                            if ($isCorrectOpt) $optClass = 'bg-green-50 border-green-300 text-green-800';
                            elseif ($isStudentPick && !$isCorrect) $optClass = 'bg-red-50 border-red-300 text-red-800';
                            else $optClass = 'bg-white border-gray-100 text-gray-600';
                            ?>
                            <div class="flex items-center gap-3 p-3 rounded-lg border <?= $optClass ?>">
                                <?php if ($isCorrectOpt): ?>
                                    <i class="fas fa-check-circle text-green-500"></i>
                                <?php elseif ($isStudentPick): ?>
                                    <i class="fas fa-times-circle text-red-500"></i>
                                <?php else: ?>
                                    <i class="far fa-circle text-gray-300"></i>
                                <?php endif; ?>
                                <span class="text-sm font-medium"><?= htmlspecialchars($opt['text'] ?? '') ?></span>
                                <?php if ($isStudentPick): ?>
                                    <span class="ml-auto text-xs font-semibold">Your Answer</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <?php if ($canRetake): ?>
            <a href="/quizzes/<?= (int)$quizId ?>/take" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">
                <i class="fas fa-redo"></i> Retake Quiz
            </a>
        <?php endif; ?>
        <a href="/quizzes" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
            <i class="fas fa-arrow-left"></i> Back to Quizzes
        </a>
    </div>
</div>
