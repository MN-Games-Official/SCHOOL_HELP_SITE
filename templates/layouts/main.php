<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'LearnHub') ?> - LearnHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
    <meta name="csrf-token" content="<?= $_SESSION['_csrf_token'] ?? '' ?>">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gray-50">

    <?php include __DIR__ . '/../components/alert.php'; ?>

    <!-- Mobile sidebar overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-gray-800/60 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-xl transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col">
        <!-- Logo -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white">
                <i class="fas fa-graduation-cap text-lg"></i>
            </div>
            <span class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-blue-500 bg-clip-text text-transparent">LearnHub</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <?php include __DIR__ . '/../components/sidebar.php'; ?>
        </nav>

        <!-- Sidebar footer / user card -->
        <div class="border-t border-gray-100 p-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                    <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></p>
                    <p class="text-xs text-gray-500 truncate capitalize"><?= htmlspecialchars($currentUser['role'] ?? 'student') ?></p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main wrapper -->
    <div class="lg:pl-64 flex flex-col min-h-screen">

        <!-- Top navbar -->
        <?php include __DIR__ . '/../components/navbar.php'; ?>

        <!-- Breadcrumb -->
        <?php if (!empty($breadcrumbs)): ?>
        <div class="px-4 sm:px-6 lg:px-8 pt-4">
            <?php $items = $breadcrumbs; include __DIR__ . '/../components/breadcrumb.php'; ?>
        </div>
        <?php endif; ?>

        <!-- Main content -->
        <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
            <?= $content ?? '' ?>
        </main>

        <!-- Footer -->
        <?php include __DIR__ . '/../components/footer.php'; ?>
    </div>

    <script src="/assets/js/app.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Auto-dismiss alerts
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
