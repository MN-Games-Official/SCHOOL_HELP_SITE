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
</head>
<body class="h-full">

    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-indigo-600 via-blue-600 to-purple-700">

        <!-- Logo -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <a href="/" class="inline-flex items-center gap-3 mb-6">
                <div class="flex items-center justify-center w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm text-white shadow-lg">
                    <i class="fas fa-graduation-cap text-2xl"></i>
                </div>
                <span class="text-3xl font-bold text-white">LearnHub</span>
            </a>
        </div>

        <!-- Flash messages -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 flex items-start gap-3" data-auto-dismiss>
                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                    <p class="text-sm text-green-800 flex-1"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
                    <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4 flex items-start gap-3" data-auto-dismiss>
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                    <p class="text-sm text-red-800 flex-1"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
                    <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
        </div>

        <!-- Card -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
            <div class="bg-white rounded-2xl shadow-2xl px-8 py-10 sm:px-10">
                <?= $content ?? '' ?>
            </div>

            <!-- Back to home -->
            <p class="mt-8 text-center text-sm text-indigo-200">
                <a href="/" class="font-medium text-white hover:text-indigo-100 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Home
                </a>
            </p>
        </div>

        <!-- Footer text -->
        <p class="mt-10 text-center text-xs text-indigo-200/70">
            &copy; <?= date('Y') ?> LearnHub. All rights reserved.
        </p>
    </div>

    <script>
        document.querySelectorAll('[data-auto-dismiss]').forEach(function(el) {
            setTimeout(function() {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 500);
            }, 5000);
        });
    </script>
</body>
</html>
