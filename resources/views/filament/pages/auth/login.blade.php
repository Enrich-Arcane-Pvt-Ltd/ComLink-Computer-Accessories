<div class="min-h-screen flex flex-col lg:flex-row">

    <div class="hidden lg:flex w-full lg:w-2/3 bg-cover bg-center relative items-center justify-center"
        style="background-image: url('{{ asset('images/login-bg.jpg') }}');">

        <div style="background-color: rgba(0, 0, 0, 0.8); position: absolute; inset: 0; z-index: 0;"></div>

        <div class="relative z-10 flex flex-col items-center text-center p-12">
            <a href="/" class="block mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="Vicky-Store"
                    class="h-32 w-auto drop-shadow-2xl hover:scale-105 transition-transform duration-300">
            </a>

            <h2 class="text-3xl font-bold text-white tracking-wider mb-2 font-heading"
                style="font-family: 'Kanit', sans-serif;">
                BUILD YOUR <span style="color: #f59e0b;">LEGACY</span>
            </h2>
            <p class="text-gray-300 max-w-md text-lg">
                Welcome to the Vicky-Store Admin Panel.
            </p>
        </div>
    </div>

    <div class="w-full lg:w-1/3 flex items-center justify-center px-6 py-10 lg:p-12 min-h-screen lg:min-h-auto"
        style="background-color: #030712;">

        <div class="w-full max-w-md space-y-8">

            <a href="/"class="lg:hidden flex justify-center mb-8" style="margin-bottom: 2rem;">

                <img src="{{ asset('images/logo.png') }}" alt="Vicky-Store" class="h-auto w-1/2">
            </a>


            <div class="text-center lg:text-left mb-10">
                <h2 class="text-2xl font-bold tracking-tight text-white">
                    Sign in to your admin dashboard
                </h2>
                <p class="mt-2 text-sm text-gray-400">
                    Please enter your credentials to continue.
                </p>
            </div>

            <form wire:submit="authenticate" class="space-y-8">

                {{ $this->form }}

                <div class="mt-6">
                    <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :full-width="true" />
                </div>

                <div class="text-center mt-6">
                    <p class="text-xs lg:text-sm text-gray-500">
                        Secure admin access powered by
                        <a href="https://enricharcane.com/" target="_blank" rel="noopener noreferrer"
                            class="hover:underline transition-all duration-200"
                            style="color: #f59e0b; font-weight: 600;">
                            Enrich Arcane
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

</div>
