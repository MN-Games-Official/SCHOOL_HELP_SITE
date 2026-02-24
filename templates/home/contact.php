<!-- Hero -->
<section class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white py-16 lg:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">Get in Touch</h1>
        <p class="mt-4 text-lg text-indigo-100 max-w-2xl mx-auto">Have a question, feedback, or just want to say hello? We'd love to hear from you.</p>
    </div>
</section>

<!-- Contact Content -->
<section class="py-16 lg:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Send us a message</h2>

                <?php if (!empty($success)): ?>
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                        <ul class="list-disc list-inside text-red-600 text-sm space-y-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="/contact" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token ?? ''); ?>">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                            <input type="text" id="name" name="name" required minlength="2" maxlength="100"
                                   value="<?php echo htmlspecialchars($old['name'] ?? ''); ?>"
                                   placeholder="John Doe"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-400">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" id="email" name="email" required maxlength="255"
                                   value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>"
                                   placeholder="john@example.com"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-400">
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="block text-sm font-semibold text-gray-700 mb-2">Subject</label>
                        <input type="text" id="subject" name="subject" required minlength="3" maxlength="200"
                               value="<?php echo htmlspecialchars($old['subject'] ?? ''); ?>"
                               placeholder="How can we help?"
                               class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-400">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                        <textarea id="message" name="message" rows="6" required minlength="10" maxlength="5000"
                                  placeholder="Tell us more about your question or feedback..."
                                  class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-400 resize-none"><?php echo htmlspecialchars($old['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors shadow-lg shadow-indigo-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Contact Info -->
                <div class="bg-gray-50 rounded-2xl p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Contact Information</h3>
                    <div class="space-y-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-700">Email</div>
                                <a href="mailto:support@aiolearning.com" class="text-indigo-600 hover:text-indigo-800 transition-colors">support@aiolearning.com</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-700">Phone</div>
                                <a href="tel:+15551234567" class="text-indigo-600 hover:text-indigo-800 transition-colors">+1 (555) 123-4567</a>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-700">Address</div>
                                <p class="text-gray-600">123 Learning Lane<br>Education City, ED 12345</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <div class="bg-gray-200 rounded-2xl h-64 flex items-center justify-center overflow-hidden">
                    <div class="text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        <p class="text-sm font-medium">Map Placeholder</p>
                    </div>
                </div>

                <!-- Office Hours -->
                <div class="bg-indigo-50 rounded-2xl p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Office Hours</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Monday – Friday</span>
                            <span class="font-semibold text-gray-900">9:00 AM – 6:00 PM</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Saturday</span>
                            <span class="font-semibold text-gray-900">10:00 AM – 4:00 PM</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sunday</span>
                            <span class="font-semibold text-gray-500">Closed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 lg:py-24 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-semibold tracking-wide uppercase">FAQ</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-extrabold text-gray-900">Frequently Asked Questions</h2>
        </div>

        <div class="space-y-4" x-data="{ open: null }">
            <!-- Q1 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="text-base font-semibold text-gray-900">Is AIO Learning free to use?</span>
                    <svg class="faq-icon w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-content hidden px-6 pb-5">
                    <p class="text-gray-500">Yes! AIO Learning offers a generous free tier that includes access to many courses, the AI tutor, flashcards, and the community forum. We also offer premium plans with additional features for power users.</p>
                </div>
            </div>

            <!-- Q2 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="text-base font-semibold text-gray-900">How does the AI tutor work?</span>
                    <svg class="faq-icon w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-content hidden px-6 pb-5">
                    <p class="text-gray-500">Our AI tutor uses advanced language models to understand your questions and provide personalized explanations. It adapts to your learning level and can help with homework, exam prep, and concept clarification across many subjects.</p>
                </div>
            </div>

            <!-- Q3 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="text-base font-semibold text-gray-900">Can teachers create their own courses?</span>
                    <svg class="faq-icon w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-content hidden px-6 pb-5">
                    <p class="text-gray-500">Absolutely! Teachers can register for a teacher account and use our intuitive course builder to create rich, interactive courses complete with lessons, quizzes, and assignments. You retain full control over your content.</p>
                </div>
            </div>

            <!-- Q4 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="text-base font-semibold text-gray-900">What subjects are available?</span>
                    <svg class="faq-icon w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-content hidden px-6 pb-5">
                    <p class="text-gray-500">We cover a wide range of subjects including Mathematics, Science, Computer Science, Languages, History, and more. Our catalog is constantly growing as new teachers join and create courses.</p>
                </div>
            </div>

            <!-- Q5 -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <button onclick="toggleFaq(this)" class="w-full flex items-center justify-between px-6 py-5 text-left focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                    <span class="text-base font-semibold text-gray-900">How do I contact support?</span>
                    <svg class="faq-icon w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-content hidden px-6 pb-5">
                    <p class="text-gray-500">You can reach our support team via the contact form above, by emailing support@aiolearning.com, or by calling +1 (555) 123-4567 during office hours. We typically respond within 24 hours.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function toggleFaq(button) {
    const content = button.nextElementSibling;
    const icon = button.querySelector('.faq-icon');
    const isHidden = content.classList.contains('hidden');

    document.querySelectorAll('.faq-content').forEach(function (el) { el.classList.add('hidden'); });
    document.querySelectorAll('.faq-icon').forEach(function (el) { el.classList.remove('rotate-180'); });

    if (isHidden) {
        content.classList.remove('hidden');
        icon.classList.add('rotate-180');
    }
}
</script>
