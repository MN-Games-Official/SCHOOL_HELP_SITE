<?php
$quiz = $quiz ?? [];
$quizId = $quiz['id'] ?? 0;
$questions = $questions ?? [];
$totalQuestions = count($questions);
$timeLimit = (int)($quiz['time_limit'] ?? 30);
?>

<div class="max-w-4xl mx-auto" id="quiz-app">
    <form id="quiz-form" action="/quizzes/<?= (int)$quizId ?>/submit" method="POST">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
        <input type="hidden" name="time_taken" id="time-taken" value="0">

        <!-- Header: Title & Timer -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-6 sticky top-16 z-20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl font-bold text-gray-900 truncate"><?= htmlspecialchars($quiz['title'] ?? 'Quiz') ?></h1>
                    <div class="flex items-center gap-3 mt-1">
                        <!-- Progress bar -->
                        <div class="flex-1 max-w-xs">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                <span>Progress</span>
                                <span id="progress-text">0 / <?= $totalQuestions ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="progress-bar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Timer -->
                <div id="timer" class="flex items-center gap-2 px-4 py-2.5 bg-indigo-50 text-indigo-700 rounded-xl font-mono text-lg font-bold flex-shrink-0">
                    <i class="fas fa-clock"></i>
                    <span id="timer-display"><?= sprintf('%02d:%02d', $timeLimit, 0) ?></span>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Questions area -->
            <div class="flex-1 min-w-0">
                <?php foreach ($questions as $qIndex => $question): ?>
                <div class="question-card bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6 <?= $qIndex > 0 ? 'hidden' : '' ?>" data-question="<?= $qIndex ?>">
                    <div class="flex items-start gap-4 mb-6">
                        <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                            <?= $qIndex + 1 ?>
                        </span>
                        <div>
                            <p class="text-xs text-gray-400 font-medium uppercase tracking-wider mb-1">Question <?= $qIndex + 1 ?> of <?= $totalQuestions ?></p>
                            <h2 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($question['text'] ?? '') ?></h2>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="space-y-3 ml-14">
                        <?php foreach (($question['options'] ?? []) as $optIndex => $option): ?>
                        <label class="group flex items-center gap-4 p-4 rounded-xl border-2 border-gray-100 hover:border-indigo-200 hover:bg-indigo-50/50 cursor-pointer transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                            <input type="radio"
                                   name="answers[<?= (int)($question['id'] ?? $qIndex) ?>]"
                                   value="<?= htmlspecialchars($option['id'] ?? $optIndex) ?>"
                                   class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 quiz-answer"
                                   data-question-index="<?= $qIndex ?>">
                            <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-gray-100 group-hover:bg-indigo-100 flex items-center justify-center text-xs font-bold text-gray-500 group-hover:text-indigo-600 transition-colors">
                                <?= chr(65 + $optIndex) ?>
                            </span>
                            <span class="text-gray-700 font-medium"><?= htmlspecialchars($option['text'] ?? '') ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Navigation buttons -->
                <div class="flex items-center justify-between gap-4">
                    <button type="button" id="prev-btn" onclick="navigateQuestion(-1)"
                            class="inline-flex items-center gap-2 px-5 py-3 bg-white border border-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" id="next-btn" onclick="navigateQuestion(1)"
                            class="inline-flex items-center gap-2 px-5 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="button" id="submit-btn" onclick="confirmSubmit()"
                            class="hidden inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-colors shadow-md">
                        <i class="fas fa-paper-plane"></i> Submit Quiz
                    </button>
                </div>
            </div>

            <!-- Question navigator sidebar -->
            <aside class="w-full lg:w-56 flex-shrink-0 order-first lg:order-last">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 sticky top-44">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Questions</h3>
                    <div class="grid grid-cols-5 lg:grid-cols-4 gap-2" id="question-nav">
                        <?php for ($i = 0; $i < $totalQuestions; $i++): ?>
                        <button type="button" onclick="goToQuestion(<?= $i ?>)"
                                class="question-dot w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold border-2 border-gray-200 text-gray-500 bg-white hover:border-indigo-300 transition-colors <?= $i === 0 ? 'ring-2 ring-indigo-500 ring-offset-1' : '' ?>"
                                data-dot="<?= $i ?>">
                            <?= $i + 1 ?>
                        </button>
                        <?php endfor; ?>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 space-y-2 text-xs text-gray-500">
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-green-500 inline-block"></span> Answered</div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-gray-200 inline-block"></span> Unanswered</div>
                        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded bg-indigo-500 inline-block"></span> Current</div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
(function() {
    let currentQuestion = 0;
    const totalQuestions = <?= $totalQuestions ?>;
    const timeLimitSeconds = <?= $timeLimit ?> * 60;
    let remainingSeconds = timeLimitSeconds;
    const answered = new Set();

    // Timer
    const timerDisplay = document.getElementById('timer-display');
    const timerEl = document.getElementById('timer');
    const timerInterval = setInterval(function() {
        remainingSeconds--;
        if (remainingSeconds <= 0) {
            clearInterval(timerInterval);
            document.getElementById('time-taken').value = timeLimitSeconds;
            document.getElementById('quiz-form').submit();
            return;
        }
        const mins = Math.floor(remainingSeconds / 60);
        const secs = remainingSeconds % 60;
        timerDisplay.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        // Warning colors
        if (remainingSeconds <= 60) {
            timerEl.classList.remove('bg-indigo-50', 'text-indigo-700', 'bg-yellow-50', 'text-yellow-700');
            timerEl.classList.add('bg-red-50', 'text-red-700');
        } else if (remainingSeconds <= 300) {
            timerEl.classList.remove('bg-indigo-50', 'text-indigo-700');
            timerEl.classList.add('bg-yellow-50', 'text-yellow-700');
        }
    }, 1000);

    // Track answers
    document.querySelectorAll('.quiz-answer').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const qi = parseInt(this.dataset.questionIndex);
            answered.add(qi);
            updateNav();
        });
    });

    function updateNav() {
        document.querySelectorAll('.question-dot').forEach(function(dot) {
            const idx = parseInt(dot.dataset.dot);
            dot.classList.remove('bg-green-500', 'text-white', 'border-green-500', 'ring-2', 'ring-indigo-500', 'ring-offset-1');
            if (idx === currentQuestion) {
                dot.classList.add('ring-2', 'ring-indigo-500', 'ring-offset-1');
            }
            if (answered.has(idx)) {
                dot.classList.add('bg-green-500', 'text-white', 'border-green-500');
            }
        });
        // Progress
        document.getElementById('progress-text').textContent = answered.size + ' / ' + totalQuestions;
        document.getElementById('progress-bar').style.width = (answered.size / totalQuestions * 100) + '%';
    }

    window.navigateQuestion = function(dir) {
        showQuestion(currentQuestion + dir);
    };

    window.goToQuestion = function(idx) {
        showQuestion(idx);
    };

    function showQuestion(idx) {
        if (idx < 0 || idx >= totalQuestions) return;
        document.querySelectorAll('.question-card').forEach(function(card) { card.classList.add('hidden'); });
        document.querySelector('[data-question="' + idx + '"]').classList.remove('hidden');
        currentQuestion = idx;
        document.getElementById('prev-btn').disabled = idx === 0;
        const isLast = idx === totalQuestions - 1;
        document.getElementById('next-btn').classList.toggle('hidden', isLast);
        document.getElementById('submit-btn').classList.toggle('hidden', !isLast);
        updateNav();
    }

    window.confirmSubmit = function() {
        const unanswered = totalQuestions - answered.size;
        let msg = 'Are you sure you want to submit this quiz?';
        if (unanswered > 0) msg = 'You have ' + unanswered + ' unanswered question(s). Submit anyway?';
        if (confirm(msg)) {
            document.getElementById('time-taken').value = timeLimitSeconds - remainingSeconds;
            document.getElementById('quiz-form').submit();
        }
    };

    updateNav();
})();
</script>
<?php $scripts = ob_get_clean(); ?>
