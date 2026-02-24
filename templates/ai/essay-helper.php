<?php
$scripts = '/assets/js/ai-chat.js';
$essay = $essay ?? '';
$prompt = $prompt ?? '';
$feedback = $feedback ?? null;
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">AI Essay Helper</h1>
        <p class="mt-1 text-gray-500">Get AI-powered feedback on your essays to improve your writing</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Panel: Essay Input -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-pen-fancy text-indigo-500"></i> Your Essay
                </h2>
            </div>
            <form id="essay-form" class="flex flex-col flex-1 p-6 gap-4">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                <div>
                    <label for="essay-prompt" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Essay Prompt / Topic</label>
                    <input type="text" id="essay-prompt" name="prompt" value="<?= htmlspecialchars($prompt) ?>"
                        placeholder="e.g., Discuss the impact of climate change on biodiversity"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                </div>
                <div class="flex-1 flex flex-col">
                    <label for="essay-text" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Essay Text</label>
                    <textarea id="essay-text" name="essay" rows="16" placeholder="Paste or type your essay here..."
                        class="flex-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700 resize-none leading-relaxed"><?= htmlspecialchars($essay) ?></textarea>
                    <div class="flex justify-between mt-2 text-xs text-gray-400">
                        <span id="word-count">0 words</span>
                        <span id="char-count">0 characters</span>
                    </div>
                </div>
                <button type="submit" id="analyze-btn" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-magic"></i> Analyze Essay
                </button>
            </form>
        </div>

        <!-- Right Panel: AI Feedback -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-comments text-indigo-500"></i> AI Feedback
                </h2>
            </div>

            <!-- Loading State -->
            <div id="feedback-loading" class="hidden flex-1 flex items-center justify-center p-6">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 relative">
                        <div class="absolute inset-0 border-4 border-indigo-200 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
                    </div>
                    <p class="text-sm font-medium text-gray-600">Analyzing your essay...</p>
                    <p class="text-xs text-gray-400 mt-1">This may take a moment</p>
                </div>
            </div>

            <?php if ($feedback): ?>
                <!-- Feedback Content -->
                <div id="feedback-content" class="flex-1 overflow-y-auto p-6 space-y-6">
                    <!-- Overall Score -->
                    <div class="text-center p-6 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Overall Score</p>
                        <div class="text-5xl font-extrabold text-indigo-600 mb-1"><?= (int)($feedback['score'] ?? 0) ?><span class="text-2xl text-gray-400">/100</span></div>
                        <?php
                        $score = $feedback['score'] ?? 0;
                        $ratingLabel = $score >= 90 ? 'Excellent' : ($score >= 80 ? 'Great' : ($score >= 70 ? 'Good' : ($score >= 60 ? 'Fair' : 'Needs Work')));
                        $ratingColor = $score >= 90 ? 'text-green-600' : ($score >= 80 ? 'text-blue-600' : ($score >= 70 ? 'text-yellow-600' : ($score >= 60 ? 'text-orange-600' : 'text-red-600')));
                        ?>
                        <p class="text-sm font-bold <?= $ratingColor ?>"><?= $ratingLabel ?></p>
                    </div>

                    <!-- Section Scores -->
                    <?php
                    $sections = [
                        'grammar' => ['label' => 'Grammar & Spelling', 'icon' => 'fa-spell-check', 'color' => 'blue'],
                        'structure' => ['label' => 'Structure & Organization', 'icon' => 'fa-sitemap', 'color' => 'purple'],
                        'content' => ['label' => 'Content & Arguments', 'icon' => 'fa-lightbulb', 'color' => 'amber'],
                    ];
                    foreach ($sections as $key => $section):
                        $sectionScore = $feedback[$key]['score'] ?? 0;
                    ?>
                        <div class="border border-gray-100 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                    <i class="fas <?= $section['icon'] ?> text-<?= $section['color'] ?>-500"></i>
                                    <?= $section['label'] ?>
                                </h3>
                                <span class="text-sm font-bold text-gray-700"><?= (int)$sectionScore ?>/100</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                                <div class="bg-<?= $section['color'] ?>-500 h-2 rounded-full transition-all duration-500" style="width: <?= (int)$sectionScore ?>%"></div>
                            </div>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($feedback[$key]['feedback'] ?? '') ?></p>
                        </div>
                    <?php endforeach; ?>

                    <!-- Improvement Suggestions -->
                    <?php if (!empty($feedback['suggestions'])): ?>
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                                <i class="fas fa-list-check text-green-500"></i> Suggestions for Improvement
                            </h3>
                            <div class="space-y-3">
                                <?php foreach ($feedback['suggestions'] as $i => $suggestion): ?>
                                    <div class="flex gap-3 p-3 bg-green-50 rounded-xl border border-green-100">
                                        <div class="w-6 h-6 rounded-full bg-green-200 flex items-center justify-center flex-shrink-0 mt-0.5">
                                            <span class="text-xs font-bold text-green-700"><?= $i + 1 ?></span>
                                        </div>
                                        <p class="text-sm text-gray-700"><?= htmlspecialchars($suggestion) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div id="feedback-empty" class="flex-1 flex items-center justify-center p-6">
                    <div class="text-center">
                        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-file-lines text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-1">No feedback yet</h3>
                        <p class="text-sm text-gray-500 max-w-xs">Enter your essay prompt and text, then click "Analyze Essay" to get AI feedback.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
