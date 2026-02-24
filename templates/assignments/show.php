<?php
$assignment = $assignment ?? [];
$assignmentId = $assignment['id'] ?? 0;
$isTeacher = ($currentUser['role'] ?? '') === 'teacher';
$isStudent = ($currentUser['role'] ?? '') === 'student';
$submissions = $submissions ?? [];
$mySubmission = $mySubmission ?? null;
$attachments = $assignment['attachments'] ?? [];
$dueDate = $assignment['due_date'] ?? '';
$isOverdue = !empty($dueDate) && strtotime($dueDate) < time();
$maxScore = $assignment['max_score'] ?? 100;
?>

<div class="max-w-4xl mx-auto">
    <!-- Back link -->
    <a href="/assignments" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors mb-6">
        <i class="fas fa-arrow-left"></i> Back to Assignments
    </a>

    <!-- Assignment Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
        <span class="inline-block px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-medium mb-3">
            <?= htmlspecialchars($assignment['course_name'] ?? 'General') ?>
        </span>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3"><?= htmlspecialchars($assignment['title'] ?? '') ?></h1>

        <?php if (!empty($assignment['description'])): ?>
            <p class="text-gray-600 leading-relaxed mb-4"><?= htmlspecialchars($assignment['description']) ?></p>
        <?php endif; ?>

        <!-- Meta info -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-5 pt-5 border-t border-gray-100">
            <!-- Due Date -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg <?= $isOverdue ? 'bg-red-100' : 'bg-blue-100' ?> flex items-center justify-center">
                    <i class="fas fa-calendar-alt <?= $isOverdue ? 'text-red-600' : 'text-blue-600' ?>"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium">Due Date</p>
                    <?php if (!empty($dueDate)): ?>
                        <p class="text-sm font-semibold <?= $isOverdue ? 'text-red-600' : 'text-gray-900' ?>">
                            <?= htmlspecialchars(date('M j, Y g:i A', strtotime($dueDate))) ?>
                        </p>
                        <?php if (!$isOverdue): ?>
                            <?php
                            $diff = strtotime($dueDate) - time();
                            $daysLeft = floor($diff / 86400);
                            $hoursLeft = floor(($diff % 86400) / 3600);
                            ?>
                            <p class="text-xs <?= $daysLeft <= 1 ? 'text-red-500 font-semibold' : 'text-gray-400' ?>">
                                <?php if ($daysLeft > 0): ?>
                                    <?= $daysLeft ?> day(s), <?= $hoursLeft ?> hour(s) left
                                <?php else: ?>
                                    <?= $hoursLeft ?> hour(s) left
                                <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <p class="text-xs text-red-500 font-semibold">Overdue</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">No deadline</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Max Score -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-star text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium">Max Score</p>
                    <p class="text-sm font-semibold text-gray-900"><?= (int)$maxScore ?> points</p>
                </div>
            </div>
            <!-- Submission Type -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-upload text-purple-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium">Submission</p>
                    <p class="text-sm font-semibold text-gray-900"><?= !empty($assignment['allow_file_upload']) ? 'Text & File Upload' : 'Text Only' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions -->
    <?php if (!empty($assignment['instructions'])): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-list-alt text-indigo-400 mr-2"></i>Instructions</h2>
        <div class="prose prose-indigo max-w-none prose-p:text-gray-600 prose-p:leading-relaxed">
            <?= $assignment['instructions'] ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Attachments -->
    <?php if (!empty($attachments)): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-paperclip text-indigo-400 mr-2"></i>Attachments</h2>
        <div class="space-y-2">
            <?php foreach ($attachments as $att): ?>
                <a href="<?= htmlspecialchars($att['url'] ?? '#') ?>" download
                   class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-indigo-50 transition-colors group">
                    <i class="fas fa-file-download text-gray-400 group-hover:text-indigo-600"></i>
                    <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700"><?= htmlspecialchars($att['name'] ?? $att['filename'] ?? 'File') ?></span>
                    <span class="ml-auto text-xs text-gray-400"><?= htmlspecialchars($att['size'] ?? '') ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Student: Submit Work -->
    <?php if ($isStudent): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-paper-plane text-indigo-400 mr-2"></i>Your Submission</h2>

        <?php if ($mySubmission && !empty($mySubmission['grade'])): ?>
            <!-- Graded submission -->
            <div class="bg-green-50 border border-green-200 rounded-xl p-5 mb-4">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fas fa-check-circle text-green-600 text-lg"></i>
                    <span class="font-bold text-green-800">Graded: <?= htmlspecialchars($mySubmission['grade']) ?>/<?= (int)$maxScore ?></span>
                </div>
                <?php if (!empty($mySubmission['feedback'])): ?>
                    <p class="text-sm text-green-700 mt-2"><strong>Feedback:</strong> <?= htmlspecialchars($mySubmission['feedback']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="/assignments/<?= (int)$assignmentId ?>/submit" method="POST" enctype="multipart/form-data" class="space-y-5">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
            <div>
                <label for="submission_content" class="block text-sm font-semibold text-gray-700 mb-2">Your Work</label>
                <textarea id="submission_content" name="content" rows="8"
                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors text-gray-900"
                          placeholder="Write your submission here..."><?= htmlspecialchars($mySubmission['content'] ?? '') ?></textarea>
            </div>
            <?php if (!empty($assignment['allow_file_upload'])): ?>
            <div>
                <label for="submission_file" class="block text-sm font-semibold text-gray-700 mb-2">Upload File</label>
                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-xl hover:border-indigo-300 transition-colors">
                    <div class="space-y-2 text-center">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-300"></i>
                        <div class="text-sm text-gray-500">
                            <label for="submission_file" class="cursor-pointer font-semibold text-indigo-600 hover:text-indigo-500">Choose a file</label>
                        </div>
                        <p class="text-xs text-gray-400">Max 10MB</p>
                        <input id="submission_file" name="submission_file" type="file" class="sr-only">
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors shadow-sm">
                    <i class="fas fa-paper-plane"></i> <?= $mySubmission ? 'Update Submission' : 'Submit' ?>
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Teacher: Submissions List -->
    <?php if ($isTeacher && !empty($submissions)): ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900">Student Submissions (<?= count($submissions) ?>)</h2>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($submissions as $sub): ?>
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                                <?= strtoupper(substr($sub['student_name'] ?? 'S', 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900"><?= htmlspecialchars($sub['student_name'] ?? 'Student') ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($sub['submitted_at'] ?? '') ?></p>
                            </div>
                        </div>
                        <?php if (!empty($sub['grade'])): ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($sub['grade']) ?>/<?= (int)$maxScore ?>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold">
                                <i class="fas fa-clock"></i> Needs Grading
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($sub['content'])): ?>
                        <div class="bg-gray-50 rounded-lg p-4 mb-4 text-sm text-gray-700 max-h-40 overflow-y-auto">
                            <?= htmlspecialchars($sub['content']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($sub['file_url'])): ?>
                        <a href="<?= htmlspecialchars($sub['file_url']) ?>" class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:underline mb-4">
                            <i class="fas fa-file-download"></i> Download submitted file
                        </a>
                    <?php endif; ?>

                    <!-- Grade form -->
                    <form action="/assignments/<?= (int)$assignmentId ?>/grade/<?= (int)$sub['id'] ?>" method="POST" class="bg-gray-50 rounded-xl p-4 mt-3">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Score</label>
                                <input type="number" name="grade" min="0" max="<?= (int)$maxScore ?>"
                                       value="<?= htmlspecialchars($sub['grade'] ?? '') ?>"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                       placeholder="0-<?= (int)$maxScore ?>">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Feedback</label>
                                <textarea name="feedback" rows="1"
                                          class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                          placeholder="Write feedback..."><?= htmlspecialchars($sub['feedback'] ?? '') ?></textarea>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors text-sm">
                                    <i class="fas fa-save mr-1"></i> Save Grade
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
