<?php
$scripts = '/assets/js/ai-chat.js';
$subjects = $subjects ?? ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'History', 'English', 'Computer Science', 'Economics'];
$quiz = $quiz ?? null;
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">AI Quiz Generator</h1>
        <p class="mt-1 text-gray-500">Generate custom quizzes on any topic with AI</p>
    </div>

    <!-- Generator Form -->
    <div id="generator-form-section" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
            <i class="fas fa-cog text-indigo-500"></i> Quiz Settings
        </h2>
        <form id="quiz-generator-form" class="space-y-6">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="subject" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Subject</label>
                    <select id="subject" name="subject" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                        <option value="">Select a subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= htmlspecialchars($subject) ?>"><?= htmlspecialchars($subject) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="topic" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Topic</label>
                    <input type="text" id="topic" name="topic" required placeholder="e.g., Photosynthesis, World War II, Derivatives"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label for="num-questions" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">
                        Number of Questions: <span id="num-questions-value" class="text-indigo-600">10</span>
                    </label>
                    <input type="range" id="num-questions" name="num_questions" min="5" max="20" value="10"
                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                        oninput="document.getElementById('num-questions-value').textContent = this.value">
                    <div class="flex justify-between text-xs text-gray-400 mt-1">
                        <span>5</span>
                        <span>20</span>
                    </div>
                </div>
                <div>
                    <label for="difficulty" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Difficulty Level</label>
                    <select id="difficulty" name="difficulty" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                        <option value="easy">Easy</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard</option>
                        <option value="mixed">Mixed</option>
                    </select>
                </div>
            </div>

            <button type="submit" id="generate-btn" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-wand-magic-sparkles"></i> Generate Quiz
            </button>
        </form>
    </div>

    <!-- Loading State -->
    <div id="quiz-loading" class="hidden bg-white rounded-2xl shadow-sm border border-gray-100 p-12 mb-8">
        <div class="text-center">
            <div class="w-20 h-20 mx-auto mb-6 relative">
                <div class="absolute inset-0 border-4 border-indigo-200 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
            </div>
            <p class="text-lg font-semibold text-gray-700">Generating your quiz...</p>
            <p class="text-sm text-gray-400 mt-2">Our AI is crafting questions tailored to your specifications</p>
        </div>
    </div>

    <!-- Quiz Results -->
    <div id="quiz-results" class="<?= $quiz ? '' : 'hidden' ?>">
        <?php if ($quiz): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-clipboard-list text-indigo-500"></i> Generated Quiz
                        </h2>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($quiz['subject'] ?? '') ?> &mdash; <?= htmlspecialchars($quiz['topic'] ?? '') ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold"><?= count($quiz['questions'] ?? []) ?> Questions</span>
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold capitalize"><?= htmlspecialchars($quiz['difficulty'] ?? 'medium') ?></span>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <?php foreach (($quiz['questions'] ?? []) as $i => $question): ?>
                        <div class="border border-gray-100 rounded-xl p-5 hover:border-indigo-100 transition-colors">
                            <div class="flex gap-3 mb-3">
                                <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-sm font-bold text-indigo-600"><?= $i + 1 ?></span>
                                <p class="text-sm font-medium text-gray-800 pt-1"><?= htmlspecialchars($question['text'] ?? '') ?></p>
                            </div>
                            <?php if (!empty($question['options'])): ?>
                                <div class="ml-11 space-y-2">
                                    <?php foreach ($question['options'] as $j => $option): ?>
                                        <?php $optLetter = chr(65 + $j); ?>
                                        <div class="flex items-center gap-3 p-2.5 rounded-lg <?= ($option === ($question['answer'] ?? '')) ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-100' ?>">
                                            <span class="w-6 h-6 rounded-full <?= ($option === ($question['answer'] ?? '')) ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600' ?> flex items-center justify-center text-xs font-bold flex-shrink-0"><?= $optLetter ?></span>
                                            <span class="text-sm text-gray-700"><?= htmlspecialchars($option) ?></span>
                                            <?php if ($option === ($question['answer'] ?? '')): ?>
                                                <i class="fas fa-check text-green-500 text-xs ml-auto"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 mb-8">
                <button id="save-quiz-btn" class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors shadow-sm">
                    <i class="fas fa-save"></i> Save as Quiz
                </button>
                <button id="regenerate-btn" class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                    <i class="fas fa-rotate"></i> Regenerate
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
