<section class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg">
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-2 text-2xl font-extrabold text-indigo-600">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                AIO Learning
            </a>
            <h1 class="mt-4 text-3xl font-extrabold text-gray-900">Create your account</h1>
            <p class="mt-2 text-gray-500">Start your learning journey today — it's free</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
            <?php if (!empty($errors)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <ul class="list-disc list-inside text-red-600 text-sm space-y-1">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/register" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" id="name" name="name" required minlength="2" maxlength="100" autocomplete="name"
                               value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>"
                               placeholder="John Doe"
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-400">
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <input type="email" id="email" name="email" required maxlength="255" autocomplete="email"
                               value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                               placeholder="you@example.com"
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-400">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"
                               placeholder="Minimum 8 characters"
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-400">
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password"
                               placeholder="Re-enter your password"
                               class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-400">
                    </div>
                </div>

                <!-- Role Selector -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">I am a...</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="student" class="peer sr-only" required
                                   <?php echo (($old['role'] ?? 'student') === 'student') ? 'checked' : ''; ?>>
                            <div class="flex flex-col items-center gap-3 p-5 rounded-xl border-2 border-gray-200 bg-white peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-gray-300 transition-all">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center peer-checked:bg-indigo-200">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                </div>
                                <span class="font-semibold text-gray-900">Student</span>
                                <span class="text-xs text-gray-500 text-center">I want to learn and take courses</span>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="role" value="teacher" class="peer sr-only"
                                   <?php echo (($old['role'] ?? '') === 'teacher') ? 'checked' : ''; ?>>
                            <div class="flex flex-col items-center gap-3 p-5 rounded-xl border-2 border-gray-200 bg-white peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-gray-300 transition-all">
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                </div>
                                <span class="font-semibold text-gray-900">Teacher</span>
                                <span class="text-xs text-gray-500 text-center">I want to create and teach courses</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="terms" name="terms" value="1" required
                           class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <label for="terms" class="text-sm text-gray-600">
                        I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-800 font-semibold">Terms of Service</a>
                        and <a href="#" class="text-indigo-600 hover:text-indigo-800 font-semibold">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit" class="w-full flex items-center justify-center px-6 py-3.5 text-base font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-lg shadow-indigo-200">
                    Create Account
                </button>
            </form>
        </div>

        <!-- Login Link -->
        <p class="mt-8 text-center text-sm text-gray-500">
            Already have an account?
            <a href="/login" class="font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Sign in instead</a>
        </p>
    </div>
</section>
