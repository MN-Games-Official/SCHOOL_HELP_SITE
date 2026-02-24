<?php
$scripts = '/assets/js/ai-chat.js';
$subjects = $subjects ?? ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'History', 'English', 'Computer Science', 'Economics'];
$messages = $messages ?? [];
$conversations = $conversations ?? [];
$activeConversation = $activeConversation ?? null;
$selectedSubject = $selectedSubject ?? '';
?>

<div class="flex h-[calc(100vh-5rem)] max-w-7xl mx-auto gap-0 lg:gap-4">

    <!-- Chat History Sidebar -->
    <div id="chat-sidebar" class="hidden lg:flex flex-col w-72 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex-shrink-0">
        <div class="p-4 border-b border-gray-100">
            <button id="new-chat-btn" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-sm">
                <i class="fas fa-plus"></i> New Chat
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-3 space-y-1">
            <?php if (empty($conversations)): ?>
                <p class="text-xs text-gray-400 text-center py-4">No conversations yet</p>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="/ai/tutor?conversation=<?= (int)$conv['id'] ?>"
                       class="block px-3 py-2.5 rounded-xl text-sm transition-colors <?= ($activeConversation == $conv['id']) ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-comment text-xs <?= ($activeConversation == $conv['id']) ? 'text-indigo-400' : 'text-gray-400' ?>"></i>
                            <span class="truncate"><?= htmlspecialchars($conv['title'] ?? 'Untitled') ?></span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5 ml-5"><?= htmlspecialchars($conv['date'] ?? '') ?></p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Chat Header -->
        <div class="flex items-center gap-3 px-4 sm:px-6 py-3 border-b border-gray-100 bg-white">
            <button id="toggle-sidebar" class="lg:hidden p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-bars"></i>
            </button>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-robot text-white text-lg"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-lg font-bold text-gray-900">AI Tutor</h1>
                <p class="text-xs text-gray-500">Your personal learning assistant</p>
            </div>
            <div>
                <select id="subject-selector" name="subject" class="px-3 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= htmlspecialchars($subject) ?>" <?= $selectedSubject === $subject ? 'selected' : '' ?>><?= htmlspecialchars($subject) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Chat Messages -->
        <div id="chat-messages" class="flex-1 overflow-y-auto px-4 sm:px-6 py-6 space-y-4">
            <?php if (empty($messages)): ?>
                <!-- Empty State -->
                <div id="empty-state" class="flex flex-col items-center justify-center h-full text-center">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center mb-6">
                        <i class="fas fa-graduation-cap text-3xl text-indigo-500"></i>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Start a conversation with your AI tutor!</h2>
                    <p class="text-gray-500 mb-8 max-w-md">Ask me anything about your studies. I can help explain concepts, solve problems, and guide your learning.</p>

                    <!-- Suggested Prompts -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-2xl w-full">
                        <button class="suggested-prompt p-4 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl border border-indigo-100 hover:border-indigo-300 hover:shadow-md transition-all text-left group">
                            <i class="fas fa-atom text-indigo-500 mb-2"></i>
                            <p class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Explain quantum physics</p>
                        </button>
                        <button class="suggested-prompt p-4 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl border border-purple-100 hover:border-purple-300 hover:shadow-md transition-all text-left group">
                            <i class="fas fa-calculator text-purple-500 mb-2"></i>
                            <p class="text-sm font-medium text-gray-700 group-hover:text-purple-700">Help me with calculus</p>
                        </button>
                        <button class="suggested-prompt p-4 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl border border-amber-100 hover:border-amber-300 hover:shadow-md transition-all text-left group">
                            <i class="fas fa-landmark text-amber-500 mb-2"></i>
                            <p class="text-sm font-medium text-gray-700 group-hover:text-amber-700">What are the causes of WWII?</p>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php if (($msg['role'] ?? '') === 'user'): ?>
                        <!-- User Message -->
                        <div class="flex justify-end">
                            <div class="max-w-[80%] sm:max-w-[70%]">
                                <div class="bg-indigo-600 text-white px-5 py-3 rounded-2xl rounded-br-md shadow-sm">
                                    <p class="text-sm leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($msg['content'] ?? '') ?></p>
                                </div>
                                <p class="text-xs text-gray-400 mt-1 text-right"><?= htmlspecialchars($msg['time'] ?? '') ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- AI Message -->
                        <div class="flex justify-start">
                            <div class="max-w-[80%] sm:max-w-[70%] flex gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-robot text-white text-xs"></i>
                                </div>
                                <div>
                                    <div class="bg-gray-100 text-gray-800 px-5 py-3 rounded-2xl rounded-bl-md">
                                        <p class="text-sm leading-relaxed whitespace-pre-wrap"><?= htmlspecialchars($msg['content'] ?? '') ?></p>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($msg['time'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Loading Indicator -->
            <div id="ai-loading" class="hidden flex justify-start">
                <div class="flex gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center flex-shrink-0 mt-1">
                        <i class="fas fa-robot text-white text-xs"></i>
                    </div>
                    <div class="bg-gray-100 px-5 py-4 rounded-2xl rounded-bl-md">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Input -->
        <div class="border-t border-gray-100 px-4 sm:px-6 py-4 bg-white">
            <form id="chat-form" class="flex items-end gap-3">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                <div class="flex-1 relative">
                    <textarea id="chat-input" name="message" rows="1" placeholder="Ask your AI tutor anything..."
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700 resize-none overflow-hidden"
                        style="max-height: 120px;" aria-label="Type your message"></textarea>
                </div>
                <button type="submit" id="send-btn" class="flex-shrink-0 w-12 h-12 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors flex items-center justify-center shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" aria-label="Send message">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2 text-center">AI responses are generated and may not always be accurate. Verify important information.</p>
        </div>
    </div>
</div>
