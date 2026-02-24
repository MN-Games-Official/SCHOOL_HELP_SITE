<?php
$courses = $courses ?? [];
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="/quizzes" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-4">
            <i class="fas fa-arrow-left"></i> Back to Quizzes
        </a>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create New Quiz</h1>
        <p class="mt-2 text-gray-500">Build a quiz with multiple-choice questions</p>
    </div>

    <form id="quiz-create-form" action="/quizzes" method="POST" class="space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

        <!-- Basic Info -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Quiz Details</h2>
            <div class="space-y-5">
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" required
                           value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900"
                           placeholder="Enter quiz title">
                </div>
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900"
                              placeholder="Brief description of the quiz"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                </div>
                <div>
                    <label for="course_id" class="block text-sm font-semibold text-gray-700 mb-2">Course <span class="text-red-500">*</span></label>
                    <select id="course_id" name="course_id" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700">
                        <option value="">Select a course</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= ($old['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-5">Quiz Settings</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div>
                    <label for="time_limit" class="block text-sm font-semibold text-gray-700 mb-2">Time Limit (min) <span class="text-red-500">*</span></label>
                    <input type="number" id="time_limit" name="time_limit" min="1" required
                           value="<?= htmlspecialchars($old['time_limit'] ?? '30') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                </div>
                <div>
                    <label for="passing_score" class="block text-sm font-semibold text-gray-700 mb-2">Passing Score (%) <span class="text-red-500">*</span></label>
                    <input type="number" id="passing_score" name="passing_score" min="0" max="100" required
                           value="<?= htmlspecialchars($old['passing_score'] ?? '70') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                </div>
                <div>
                    <label for="max_attempts" class="block text-sm font-semibold text-gray-700 mb-2">Max Attempts</label>
                    <input type="number" id="max_attempts" name="max_attempts" min="0"
                           value="<?= htmlspecialchars($old['max_attempts'] ?? '0') ?>"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900">
                    <p class="mt-1 text-xs text-gray-400">0 = unlimited</p>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-900">Questions</h2>
                <button type="button" onclick="addQuestion()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors text-sm">
                    <i class="fas fa-plus"></i> Add Question
                </button>
            </div>
            <div id="questions-container" class="space-y-6">
                <!-- Questions will be added here by JS -->
            </div>
            <div id="no-questions" class="text-center py-12 text-gray-400">
                <i class="fas fa-clipboard-list text-4xl mb-3"></i>
                <p class="font-semibold">No questions yet</p>
                <p class="text-sm mt-1">Click "Add Question" to get started</p>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-4">
            <a href="/quizzes" class="px-6 py-3 text-gray-700 font-semibold rounded-xl hover:bg-gray-100 transition-colors">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md">
                <i class="fas fa-save"></i> Create Quiz
            </button>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
(function() {
    let questionCount = 0;
    const container = document.getElementById('questions-container');
    const noQuestions = document.getElementById('no-questions');

    window.addQuestion = function() {
        const idx = questionCount++;
        noQuestions.classList.add('hidden');

        const html = `
        <div class="question-block border border-gray-200 rounded-xl p-5" data-q-idx="${idx}">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-800">Question ${idx + 1}</h3>
                <button type="button" onclick="removeQuestion(this)" class="text-red-400 hover:text-red-600 transition-colors text-sm" title="Remove">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Question Text <span class="text-red-500">*</span></label>
                <input type="text" name="questions[${idx}][text]" required
                       class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900"
                       placeholder="Enter your question">
            </div>
            <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">Answer Options</label>
                ${[0,1,2,3].map(o => `
                <div class="flex items-center gap-3">
                    <input type="radio" name="questions[${idx}][correct]" value="${o}" ${o === 0 ? 'required' : ''}
                           class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500" title="Mark as correct">
                    <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">${String.fromCharCode(65+o)}</span>
                    <input type="text" name="questions[${idx}][options][${o}]" required
                           class="flex-1 px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-900 text-sm"
                           placeholder="Option ${String.fromCharCode(65+o)}">
                </div>`).join('')}
                <p class="text-xs text-gray-400 ml-7"><i class="fas fa-info-circle mr-1"></i>Select the radio button next to the correct answer.</p>
            </div>
        </div>`;

        container.insertAdjacentHTML('beforeend', html);
    };

    window.removeQuestion = function(btn) {
        if (!confirm('Remove this question?')) return;
        btn.closest('.question-block').remove();
        if (container.children.length === 0) {
            noQuestions.classList.remove('hidden');
        }
        // Renumber
        container.querySelectorAll('.question-block').forEach(function(block, i) {
            block.querySelector('h3').textContent = 'Question ' + (i + 1);
        });
    };

    // Start with one question
    addQuestion();
})();
</script>
<?php $scripts = ob_get_clean(); ?>
