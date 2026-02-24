<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> - LearnHub Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <meta name="csrf-token" content="<?= $_SESSION['_csrf_token'] ?? '' ?>">
</head>
<body class="h-full bg-gray-50">

    <?php include __DIR__ . '/../components/alert.php'; ?>

    <!-- Mobile sidebar overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-gray-800/60 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Admin sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 shadow-xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700/50">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white">
                <i class="fas fa-shield-halved text-lg"></i>
            </div>
            <div>
                <span class="text-lg font-bold text-white">LearnHub</span>
                <span class="block text-xs text-gray-400 -mt-0.5">Admin Panel</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <?php
            $adminNav = [
                ['label' => 'Dashboard',    'url' => '/admin',          'icon' => 'fas fa-chart-pie',   'id' => 'admin-dashboard'],
                ['label' => 'Users',         'url' => '/admin/users',    'icon' => 'fas fa-users',       'id' => 'admin-users'],
                ['label' => 'Courses',       'url' => '/admin/courses',  'icon' => 'fas fa-book-open',   'id' => 'admin-courses'],
                ['label' => 'Reports',       'url' => '/admin/reports',  'icon' => 'fas fa-chart-bar',   'id' => 'admin-reports'],
                ['label' => 'Settings',      'url' => '/admin/settings', 'icon' => 'fas fa-cog',         'id' => 'admin-settings'],
            ];
            $currentPage = $currentPage ?? '';
            foreach ($adminNav as $item):
                $isActive = ($currentPage === $item['id']);
            ?>
                <a href="<?= $item['url'] ?>"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 <?= $isActive
                       ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30'
                       : 'text-gray-300 hover:bg-gray-800 hover:text-white' ?>">
                    <i class="<?= $item['icon'] ?> w-5 text-center"></i>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>

            <div class="pt-4 mt-4 border-t border-gray-700/50">
                <a href="/dashboard"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-all duration-200">
                    <i class="fas fa-arrow-left w-5 text-center"></i>
                    <span>Back to Main Site</span>
                </a>
            </div>
        </nav>

        <!-- Sidebar footer -->
        <div class="border-t border-gray-700/50 p-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-semibold text-sm">
                    <?= strtoupper(substr($currentUser['name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></p>
                    <p class="text-xs text-gray-400 truncate">Administrator</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main wrapper -->
    <div class="lg:pl-64 flex flex-col min-h-screen">

        <!-- Top bar -->
        <header class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                <!-- Mobile toggle -->
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <h1 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($pageTitle ?? 'Admin') ?></h1>

                <!-- Right side -->
                <div class="flex items-center gap-3">
                    <a href="/dashboard" class="text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                        <i class="fas fa-external-link-alt mr-1"></i> View Site
                    </a>
                    <a href="/logout" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
            <?= $content ?? '' ?>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-xs text-gray-400">&copy; <?= date('Y') ?> LearnHub Admin Panel</p>
        </footer>
    </div>

    <script src="/assets/js/app.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        document.querySelectorAll('[data-auto-dismiss]').forEach(function(el) {
            setTimeout(function() {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 500);
            }, 5000);
        });

        function dismissAlert(btn) {
            var alert = btn.closest('[data-auto-dismiss]');
            if (alert) {
                alert.style.transition = 'opacity 0.3s';
                alert.style.opacity = '0';
                setTimeout(function() { alert.remove(); }, 300);
            }
        }
    </script>
    <?= $scripts ?? '' ?>
</body>
</html>
