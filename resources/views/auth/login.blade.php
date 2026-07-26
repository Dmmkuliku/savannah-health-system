<x-guest-layout>
    @php
        $isFreshInstall = false;
        try {
            $isFreshInstall = \Illuminate\Support\Facades\Schema::hasTable('users')
                && \App\Models\User::query()->count() <= 1;
        } catch (\Throwable) {
            $isFreshInstall = true;
        }
    @endphp
    <div class="mb-7">
        <div class="mb-4 flex items-center gap-3 lg:hidden">
            <x-savannah-mark class="h-10 w-10" />
            <p class="font-display text-lg font-bold text-ink-900 dark:text-white">Savannah Health</p>
        </div>
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-700 dark:text-brand-300">Hospital workstation</p>
        <h2 class="mt-2 font-display text-3xl text-ink-900 dark:text-white">Staff sign in</h2>
        <p class="mp-muted mt-1 text-sm font-medium">Use your assigned hospital account. Only the administrator exists after a fresh install.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4" autocomplete="on">
        @csrf
        <div>
            <label class="mp-label" for="email">Work email</label>
            <input id="email" class="mp-input" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@savannah.health">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <label class="mp-label" for="password">Password</label>
            <input id="password" class="mp-input" type="password" name="password" required placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <label class="inline-flex items-center gap-2 text-sm font-semibold text-ink-800 dark:text-ink-100">
            <input type="checkbox" name="remember" class="rounded border-ink-400 text-brand-700 focus:ring-brand-600">
            Keep me signed in on this office laptop
        </label>
        <button class="mp-btn w-full">Sign in</button>
    </form>

    @if($isFreshInstall)
        <details class="mt-6 rounded-lg border-2 border-brand-600 bg-brand-50 p-3 text-xs font-medium text-brand-950 dark:border-brand-400 dark:bg-brand-950 dark:text-brand-50" open>
            <summary class="cursor-pointer font-bold">First-install administrator</summary>
            <p class="mt-2">Email: <strong>admin@savannah.health</strong></p>
            <p>Password: <strong>Savannah@Admin1</strong></p>
            <p class="mt-2 font-semibold">Change this password after first login. Then open Staff Users to register doctors, nurses, cashiers and other workers.</p>
        </details>
    @endif
</x-guest-layout>
