<?php
$currentUser = $currentUser ?? [];
$unreadNotifications = $unreadNotifications ?? 0;
$unreadMessages = $unreadMessages ?? 0;
?>
<header class="sticky top-0 z-20 bg-white border-b border-gray-200/80 shadow-sm">
    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">

        <!-- Left: Mobile menu toggle -->
        <div class="flex items-center gap-4">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors" aria-label="Toggle sidebar">
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Search bar -->
            <div class="hidden sm:block relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-sm"></i>
                </div>
                <input type="text"
                       placeholder="Search courses, topics..."
                       class="w-64 lg:w-80 pl-10 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all placeholder-gray-400">
            </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2">

            <!-- Mobile search toggle -->
            <button id="mobile-search-btn" class="sm:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors" aria-label="Toggle search" onclick="document.getElementById('mobile-search').classList.toggle('hidden')">
                <i class="fas fa-search"></i>
            </button>

            <!-- Notifications -->
            <div class="relative" id="notification-dropdown">
                <button onclick="toggleDropdown('notification-menu')" aria-label="Notifications" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors">
                    <i class="fas fa-bell text-lg"></i>
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-red-500 rounded-full ring-2 ring-white">
                            <?= $unreadNotifications > 9 ? '9+' : $unreadNotifications ?>
                        </span>
                    <?php endif; ?>
                </button>
                <div id="notification-menu" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <div class="px-4 py-2 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        <p class="px-4 py-6 text-sm text-gray-400 text-center">No new notifications</p>
                    </div>
                    <div class="px-4 py-2 border-t border-gray-100">
                        <a href="/notifications" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">View all notifications</a>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="relative" id="messages-dropdown">
                <a href="/messages" class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors inline-flex">
                    <i class="fas fa-envelope text-lg"></i>
                    <?php if ($unreadMessages > 0): ?>
                        <span class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-indigo-500 rounded-full ring-2 ring-white">
                            <?= $unreadMessages > 9 ? '9+' : $unreadMessages ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Divider -->
            <div class="hidden sm:block w-px h-6 bg-gray-200 mx-1"></div>

            <!-- User dropdown -->
            <div class="relative" id="user-dropdown">
                <button onclick="toggleDropdown('user-menu')" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-sm">
                        <?php if (!empty($currentUser['avatar'])): ?>
                            <img src="<?= htmlspecialchars($currentUser['avatar']) ?>" alt="" class="w-8 h-8 rounded-full object-cover">
                        <?php else: ?>
                            <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="hidden md:block text-sm font-medium text-gray-700 max-w-[120px] truncate"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></span>
                    <i class="hidden md:block fas fa-chevron-down text-[10px] text-gray-400"></i>
                </button>
                <div id="user-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></p>
                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                    </div>
                    <a href="/profile" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-user w-4 text-center text-gray-400"></i> My Profile
                    </a>
                    <a href="/settings" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-cog w-4 text-center text-gray-400"></i> Settings
                    </a>
                    <?php if (($currentUser['role'] ?? '') === 'admin'): ?>
                    <a href="/admin" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-shield-halved w-4 text-center text-gray-400"></i> Admin Panel
                    </a>
                    <?php endif; ?>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="/logout" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <i class="fas fa-sign-out-alt w-4 text-center"></i> Sign Out
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile search bar -->
    <div id="mobile-search" class="hidden sm:hidden border-t border-gray-100 px-4 py-3">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400 text-sm"></i>
            </div>
            <input type="text"
                   placeholder="Search..."
                   class="w-full pl-10 pr-4 py-2 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all placeholder-gray-400">
        </div>
    </div>
</header>

<script>
function toggleDropdown(menuId) {
    var menu = document.getElementById(menuId);
    var allMenus = document.querySelectorAll('#notification-menu, #user-menu');
    allMenus.forEach(function(m) {
        if (m.id !== menuId) m.classList.add('hidden');
    });
    menu.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#notification-dropdown') && !e.target.closest('#user-dropdown')) {
        document.querySelectorAll('#notification-menu, #user-menu').forEach(function(m) {
            m.classList.add('hidden');
        });
    }
});
</script>
