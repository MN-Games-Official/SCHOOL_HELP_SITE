<?php
$grades = $grades ?? [];
$courses = $courses ?? [];
$role = $role ?? ($currentUser['role'] ?? 'student');
$isTeacher = $role === 'teacher';
$isStudent = $role === 'student';
$gpa = $gpa ?? 0.0;
$letterGrade = $letterGrade ?? 'N/A';
$distribution = $distribution ?? ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
$selectedCourse = $_GET['course'] ?? '';
$classGrades = $classGrades ?? [];
$classAverage = $classAverage ?? 0;
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Grades</h1>
            <p class="mt-1 text-gray-500"><?= $isTeacher ? 'View and manage student grades' : 'Track your academic performance' ?></p>
        </div>
    </div>

    <?php if ($isStudent): ?>
        <!-- Student View -->

        <!-- GPA Display -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8 text-center">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Overall GPA</p>
            <div class="flex items-center justify-center gap-6">
                <div>
                    <span class="text-6xl font-extrabold text-indigo-600"><?= number_format($gpa, 2) ?></span>
                    <span class="text-2xl text-gray-400 font-medium"> / 4.00</span>
                </div>
                <div class="w-20 h-20 rounded-full bg-indigo-100 flex items-center justify-center">
                    <span class="text-3xl font-bold text-indigo-700"><?= htmlspecialchars($letterGrade) ?></span>
                </div>
            </div>
            <!-- GPA Progress Bar -->
            <div class="mt-6 max-w-md mx-auto">
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-indigo-600 h-3 rounded-full transition-all duration-500" style="width: <?= min(($gpa / 4.0) * 100, 100) ?>%"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Grade Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-chart-bar text-indigo-500 mr-2"></i>Grade Distribution</h2>
                <div class="space-y-3">
                    <?php
                    $distColors = ['A' => 'bg-green-500', 'B' => 'bg-blue-500', 'C' => 'bg-yellow-500', 'D' => 'bg-orange-500', 'F' => 'bg-red-500'];
                    $totalGrades = array_sum($distribution) ?: 1;
                    foreach ($distribution as $grade => $count):
                        $pct = round(($count / $totalGrades) * 100);
                    ?>
                        <div>
                            <div class="flex justify-between text-sm font-medium mb-1">
                                <span class="text-gray-700"><?= $grade ?></span>
                                <span class="text-gray-500"><?= $count ?> (<?= $pct ?>%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="<?= $distColors[$grade] ?? 'bg-gray-500' ?> h-2.5 rounded-full transition-all duration-500" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Progress Chart Placeholder -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-chart-line text-indigo-500 mr-2"></i>Progress Over Time</h2>
                <div class="flex items-center justify-center h-64 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl border-2 border-dashed border-indigo-200">
                    <div class="text-center">
                        <i class="fas fa-chart-area text-4xl text-indigo-300 mb-3"></i>
                        <p class="text-sm text-indigo-400 font-medium">Grade progress chart</p>
                        <p class="text-xs text-gray-400 mt-1">Visual trend coming soon</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grades by Course -->
        <h2 class="text-xl font-bold text-gray-900 mb-4">Grades by Course</h2>
        <?php if (empty($courses)): ?>
            <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
                <i class="fas fa-graduation-cap text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700">No grades yet</h3>
                <p class="text-gray-500 mt-1">Your grades will appear here once assignments are graded.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($courses as $course): ?>
                    <details class="group bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <summary class="flex items-center justify-between p-5 sm:p-6 cursor-pointer hover:bg-gray-50 transition-colors list-none">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-book text-indigo-600 text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($course['title'] ?? '') ?></h3>
                                    <p class="text-sm text-gray-500"><?= (int)($course['graded_count'] ?? 0) ?> graded items</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <?php
                                $avg = $course['average'] ?? 0;
                                $avgColor = $avg >= 90 ? 'text-green-600 bg-green-100' : ($avg >= 80 ? 'text-blue-600 bg-blue-100' : ($avg >= 70 ? 'text-yellow-600 bg-yellow-100' : ($avg >= 60 ? 'text-orange-600 bg-orange-100' : 'text-red-600 bg-red-100')));
                                ?>
                                <span class="px-3 py-1 rounded-full text-sm font-bold <?= $avgColor ?>"><?= number_format($avg, 1) ?>%</span>
                                <i class="fas fa-chevron-down text-gray-400 group-open:rotate-180 transition-transform duration-200"></i>
                            </div>
                        </summary>
                        <div class="border-t border-gray-100 p-5 sm:p-6">
                            <?php $courseGrades = $course['grades'] ?? []; ?>
                            <?php if (empty($courseGrades)): ?>
                                <p class="text-sm text-gray-500 text-center py-4">No graded items in this course.</p>
                            <?php else: ?>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                                <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Assignment</th>
                                                <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                                <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Score</th>
                                                <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            <?php foreach ($courseGrades as $g): ?>
                                                <?php
                                                $score = $g['score'] ?? 0;
                                                $maxScore = $g['max_score'] ?? 100;
                                                $pct = $maxScore > 0 ? round(($score / $maxScore) * 100, 1) : 0;
                                                $gradeColor = $pct >= 90 ? 'text-green-600' : ($pct >= 80 ? 'text-blue-600' : ($pct >= 70 ? 'text-yellow-600' : ($pct >= 60 ? 'text-orange-600' : 'text-red-600')));
                                                $typeIcons = ['assignment' => 'fa-file-alt', 'quiz' => 'fa-question-circle', 'exam' => 'fa-clipboard-list', 'project' => 'fa-project-diagram'];
                                                $typeIcon = $typeIcons[$g['type'] ?? 'assignment'] ?? 'fa-file-alt';
                                                ?>
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-3 px-3 font-medium text-gray-900"><?= htmlspecialchars($g['title'] ?? '') ?></td>
                                                    <td class="py-3 px-3 text-gray-500">
                                                        <span class="inline-flex items-center gap-1">
                                                            <i class="fas <?= $typeIcon ?> text-gray-400"></i>
                                                            <?= htmlspecialchars(ucfirst($g['type'] ?? 'assignment')) ?>
                                                        </span>
                                                    </td>
                                                    <td class="py-3 px-3 text-right text-gray-700"><?= $score ?> / <?= $maxScore ?></td>
                                                    <td class="py-3 px-3 text-right font-bold <?= $gradeColor ?>"><?= $pct ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Teacher View -->

        <!-- Course Selector -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 mb-8">
            <form method="GET" action="/grades" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <label for="course" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Select Course</label>
                    <select id="course" name="course" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 text-sm">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= (int)$c['id'] ?>" <?= $selectedCourse == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-sm">
                        <i class="fas fa-search mr-1"></i> View Grades
                    </button>
                </div>
            </form>
        </div>

        <!-- Class Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-users text-indigo-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Students</p>
                        <p class="text-2xl font-bold text-gray-900"><?= count($classGrades) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Class Average</p>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($classAverage, 1) ?>%</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-trophy text-yellow-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Highest</p>
                        <?php $highest = !empty($classGrades) ? max(array_column($classGrades, 'average')) : 0; ?>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($highest, 1) ?>%</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <i class="fas fa-arrow-down text-red-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase">Lowest</p>
                        <?php $lowest = !empty($classGrades) ? min(array_column($classGrades, 'average')) : 0; ?>
                        <p class="text-2xl font-bold text-gray-900"><?= number_format($lowest, 1) ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Grade Distribution -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-chart-pie text-indigo-500 mr-2"></i>Grade Distribution</h2>
                <div class="space-y-3">
                    <?php
                    $distColors = ['A' => 'bg-green-500', 'B' => 'bg-blue-500', 'C' => 'bg-yellow-500', 'D' => 'bg-orange-500', 'F' => 'bg-red-500'];
                    $totalGrades = array_sum($distribution) ?: 1;
                    foreach ($distribution as $grade => $count):
                        $pct = round(($count / $totalGrades) * 100);
                    ?>
                        <div>
                            <div class="flex justify-between text-sm font-medium mb-1">
                                <span class="text-gray-700"><?= $grade ?></span>
                                <span class="text-gray-500"><?= $count ?> (<?= $pct ?>%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="<?= $distColors[$grade] ?? 'bg-gray-500' ?> h-2.5 rounded-full transition-all duration-500" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Class Grade Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-table text-indigo-500 mr-2"></i>Student Grades</h2>
                <?php if (empty($classGrades)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-user-graduate text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Select a course to view student grades.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="text-right py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Average</th>
                                    <th class="text-center py-3 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Letter Grade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($classGrades as $sg): ?>
                                    <?php
                                    $avg = $sg['average'] ?? 0;
                                    $letter = $sg['letter_grade'] ?? 'N/A';
                                    $gradeColor = $avg >= 90 ? 'text-green-600 bg-green-100' : ($avg >= 80 ? 'text-blue-600 bg-blue-100' : ($avg >= 70 ? 'text-yellow-600 bg-yellow-100' : ($avg >= 60 ? 'text-orange-600 bg-orange-100' : 'text-red-600 bg-red-100')));
                                    ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                                    <?= strtoupper(substr($sg['name'] ?? '?', 0, 1)) ?>
                                                </div>
                                                <span class="font-medium text-gray-900"><?= htmlspecialchars($sg['name'] ?? '') ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold text-gray-900"><?= number_format($avg, 1) ?>%</td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $gradeColor ?>"><?= htmlspecialchars($letter) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
