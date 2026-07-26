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
            <p class="font-display text-lg text-ink-900">Savannah Health</p>
        </div>
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Hospital workstation</p>
        <h2 class="mt-2 font-display text-3xl text-ink-900">Staff sign in</h2>
        <p class="mt-1 text-sm text-ink-700/70">Use your assigned hospital account. Only the administrator exists after a fresh install.</p>
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
        <label class="inline-flex items-center gap-2 text-sm text-ink-700">
            <input type="checkbox" name="remember" class="rounded border-brand-300 text-brand-700 focus:ring-brand-500">
            Keep me signed in on this office laptop
        </label>
        <button class="mp-btn w-full">Sign in</button>
    </form>

    @if($isFreshInstall)
        <details class="mt-6 rounded-lg border border-brand-100 bg-brand-50/70 p-3 text-xs text-brand-900" open>
            <summary class="cursor-pointer font-semibold">First-install administrator</summary>
            <p class="mt-2">Email: <strong>admin@savannah.health</strong></p>
            <p>Password: <strong>Savannah@Admin1</strong></p>
            <p class="mt-2 text-brand-800/80">Change this password after first login. Then open Staff Users to register doctors, nurses, cashiers and other workers for this hospital.</p>
        </details>
    @endif
</x-guest-layout>
