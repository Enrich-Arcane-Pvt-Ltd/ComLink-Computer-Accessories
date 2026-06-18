<div
    class="min-h-screen flex flex-col lg:flex-row relative overflow-hidden bg-gradient-to-br from-gray-900 via-black to-gray-900">
    @vite('resources/css/app.css')
    <!-- Left Panel - Hero Section with Curved Design -->
    <div class="hidden lg:flex lg:w-[55%] relative items-center justify-center overflow-hidden">
        <!-- Background with Multiple Overlays -->
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
            style="background-image: url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1400&q=80');">
            <div class="absolute inset-0 bg-gradient-to-br from-black/85 via-gray-900/90 to-black/95"></div>
            <div class="absolute inset-0 bg-gradient-to-tr from-primary-600/15 via-transparent to-secondary-600/10">
            </div>
        </div>

        <!-- Curved Overlay Element -->
        <div class="absolute -right-20 top-0 bottom-0 w-96 bg-black backdrop-blur-sm"
            style="clip-path: ellipse(60% 100% at 100% 50%); transform: translateX(20%);"></div>


        <!-- Content Container -->
        <div class="relative z-10 flex flex-col items-center text-center px-8 py-12 max-w-2xl">
            <!-- Logo - Clickable to Home -->
            <a href="/" class="relative mb-10 group">
                <div
                    class="absolute inset-0 blur-3xl bg-primary-500/20 rounded-full scale-150 group-hover:bg-primary-500/30 transition-all duration-300">
                </div>
                <img src="{{ asset('images/logo.png') }}" alt="Vicky-Store"
                    class="relative h-44 w-auto drop-shadow-2xl group-hover:scale-105 transition-transform duration-300">
            </a>

            <!-- Main Heading -->
            <h1 class="text-5xl font-heading font-bold text-white tracking-wide mb-5 leading-tight">
                BUILD YOUR <span class="text-primary-500 drop-shadow-[0_0_30px_rgba(245,158,11,0.5)]">LEGACY</span>
            </h1>

            <!-- Subheading -->
            <p class="text-gray-300 text-xl mb-10 leading-relaxed max-w-xl">
                Welcome to the Vicky-Store Admin Panel.
            </p>

            <!-- Quote -->
            <div class="mt-14 pt-10 border-t border-white/10 w-full max-w-md">
                <blockquote class="text-primary-400 italic text-base font-light">
                    "Excellence is not a destination; it's a continuous journey that never ends."
                </blockquote>
            </div>
        </div>

        <!-- Decorative Blur Elements -->
        <div class="absolute top-1/4 right-20 w-40 h-40 bg-primary-500/10 rounded-full blur-[100px]"
            style="animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></div>
        <div class="absolute bottom-1/4 left-20 w-48 h-48 bg-secondary-500/10 rounded-full blur-[100px]"
            style="animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite; animation-delay: 2s;"></div>
    </div>

    <!-- Right Panel - Login Form with Curved Card Design -->
    <div class="w-full lg:w-[45%] flex items-center justify-center min-h-screen p-6 lg:p-8 relative">

        <!-- Login Card with Rounded Curves -->
        <div class="relative w-full max-w-md">
            <!-- Glassmorphism Card -->
            <div
                class="relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-xl rounded-[2.5rem] p-8 lg:p-10 shadow-2xl border border-gray-700/30">

                <!-- Decorative Glow -->
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-primary-600/20 to-secondary-600/20 rounded-[2.5rem] blur-xl opacity-50">
                </div>

                <!-- Card Content -->
                <div class="relative z-10">
                    <!-- Mobile Logo - Clickable -->
                    <div class="lg:hidden flex justify-center mb-8">
                        <a href="/" class="block">
                            <img src="{{ asset('images/logo.png') }}" alt="Vicky-Store"
                                class="h-20 w-auto hover:scale-105 transition-transform duration-300">
                        </a>
                    </div>

                    <!-- Form Header -->
                    <div class="text-center mb-8">
                        <div class="inline-block mb-4">
                            <div class="h-1 w-16 bg-gradient-to-r from-primary-500 to-primary-600 rounded-full"></div>
                        </div>
                        <h2 class="text-2xl lg:text-3xl font-bold tracking-tight text-white mb-2">
                            Sign in to your admin dashboard
                        </h2>
                        <p class="text-sm lg:text-base text-gray-400">
                            Please enter your credentials to continue.
                        </p>
                    </div>

                    <!-- Login Form -->
                    <form wire:submit="authenticate" class="space-y-5">
                        <!-- Form Container with Custom Styling -->
                        <div class="space-y-4">


                            <div class="login-form-container">
                                {{ $this->form }}
                            </div>
                        </div>

                        <!-- Submit Button with Gradient -->
                        <div class="mt-6">

                            <div class="login-submit-btn">
                                <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="true" />
                            </div>
                        </div>

                        <!-- Footer Info -->
                        <div class="text-center mt-6">
                            <p class="text-xs lg:text-sm text-gray-500">
                                Secure admin access powered by
                                <span class="text-primary-500 font-semibold">Vicky-Store</span>
                            </p>
                        </div>
                    </form>

                    <!-- Footer Links -->
                    <div class="flex items-center justify-center gap-6 mt-8 text-xs lg:text-sm text-gray-600">
                        <a href="#" class="hover:text-primary-500 transition-colors duration-200">Help</a>
                        <span class="text-gray-700">•</span>
                        <a href="#" class="hover:text-primary-500 transition-colors duration-200">Privacy</a>
                        <span class="text-gray-700">•</span>
                        <a href="#" class="hover:text-primary-500 transition-colors duration-200">Terms</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>