<?php
$currentPage = $currentPage ?? '';
$userRole = $currentUser['role'] ?? 'student';

$navGroups = [
    'Main' => [
        ['label' => 'Dashboard',    'url' => '/dashboard',    'icon' => 'fas fa-home',           'id' => 'dashboard'],
        ['label' => 'My Courses',   'url' => '/courses',      'icon' => 'fas fa-book-open',      'id' => 'courses'],
        ['label' => 'Assignments',  'url' => '/assignments',  'icon' => 'fas fa-tasks',          'id' => 'assignments'],
        ['label' => 'Quizzes',      'url' => '/quizzes',      'icon' => 'fas fa-question-circle','id' => 'quizzes'],
    ],
    'Study Tools' => [
        ['label' => 'Notes',        'url' => '/notes',        'icon' => 'fas fa-sticky-note',    'id' => 'notes'],
        ['label' => 'Flashcards',   'url' => '/flashcards',   'icon' => 'fas fa-clone',          'id' => 'flashcards'],
        ['label' => 'AI Tutor',     'url' => '/ai-tutor',     'icon' => 'fas fa-robot',          'id' => 'ai-tutor'],
    ],
    'Community' => [
        ['label' => 'Forum',        'url' => '/forum',        'icon' => 'fas fa-comments',       'id' => 'forum'],
        ['label' => 'Messages',     'url' => '/messages',     'icon' => 'fas fa-envelope',       'id' => 'messages'],
    ],
    'Schedule' => [
        ['label' => 'Calendar',     'url' => '/calendar',     'icon' => 'fas fa-calendar-alt',   'id' => 'calendar'],
        ['label' => 'Grades',       'url' => '/grades',       'icon' => 'fas fa-chart-line',     'id' => 'grades'],
    ],
];

// Teacher-specific items
if ($userRole === 'teacher' || $userRole === 'admin') {
    $navGroups['Teaching'] = [
        ['label' => 'Create Course', 'url' => '/courses/create', 'icon' => 'fas fa-plus-circle',  'id' => 'create-course'],
        ['label' => 'Manage Courses','url' => '/courses/manage', 'icon' => 'fas fa-chalkboard',   'id' => 'manage-courses'],
        ['label' => 'Student Grades','url' => '/grades/manage',  'icon' => 'fas fa-clipboard-check','id' => 'manage-grades'],
    ];
}

// Admin-specific items
if ($userRole === 'admin') {
    $navGroups['Administration'] = [
        ['label' => 'Admin Panel',  'url' => '/admin',         'icon' => 'fas fa-shield-halved', 'id' => 'admin-panel'],
        ['label' => 'Manage Users', 'url' => '/admin/users',   'icon' => 'fas fa-users-cog',     'id' => 'admin-users'],
        ['label' => 'Site Settings','url' => '/admin/settings', 'icon' => 'fas fa-sliders-h',     'id' => 'admin-settings'],
    ];
}

foreach ($navGroups as $groupLabel => $items): ?>
    <div class="mb-4">
        <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400"><?= $groupLabel ?></p>
        <?php foreach ($items as $item):
            $isActive = ($currentPage === $item['id']);
        ?>
            <a href="<?= $item['url'] ?>"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200
                      <?= $isActive
                          ? 'bg-indigo-50 text-indigo-700 shadow-sm border border-indigo-100'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">
                <i class="<?= $item['icon'] ?> w-5 text-center <?= $isActive ? 'text-indigo-600' : 'text-gray-400' ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
