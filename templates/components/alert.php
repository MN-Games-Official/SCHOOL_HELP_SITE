<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm w-full animate-slide-in" data-auto-dismiss>
    <div class="bg-white rounded-xl shadow-lg border border-green-200 overflow-hidden">
        <div class="flex items-start gap-3 p-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                <i class="fas fa-check text-green-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">Success</p>
                <p class="text-sm text-gray-600 mt-0.5"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
            </div>
            <button onclick="dismissAlert(this)" class="flex-shrink-0 p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="h-1 bg-green-500 animate-shrink-bar"></div>
    </div>
</div>
<?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm w-full animate-slide-in" data-auto-dismiss>
    <div class="bg-white rounded-xl shadow-lg border border-red-200 overflow-hidden">
        <div class="flex items-start gap-3 p-4">
            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-exclamation text-red-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">Error</p>
                <p class="text-sm text-gray-600 mt-0.5"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
            </div>
            <button onclick="dismissAlert(this)" class="flex-shrink-0 p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="h-1 bg-red-500 animate-shrink-bar"></div>
    </div>
</div>
<?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<style>
@keyframes slide-in {
    from { transform: translateX(100%); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}
@keyframes shrink-bar {
    from { width: 100%; }
    to   { width: 0%; }
}
.animate-slide-in  { animation: slide-in 0.4s ease-out; }
.animate-shrink-bar { animation: shrink-bar 5s linear forwards; }
</style>
